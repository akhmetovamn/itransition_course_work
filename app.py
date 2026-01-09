from flask import Flask, render_template_string, request, jsonify, session, redirect, url_for
import sqlite3
from datetime import datetime
import os

app = Flask(__name__)
app.secret_key = 'secret_keeey'  #you need to change when using
DATABASE = 'inventory.db'
USERS = {'user': '12345'}

def get_db():
    conn = sqlite3.connect(DATABASE)
    conn.row_factory = sqlite3.Row
    return conn

def get_inventories(search_query=''):
    conn = get_db()
    cur = conn.cursor()
    if search_query:
        q = f"%{search_query}%"
        cur.execute(
            "SELECT id, title, category, item_count, owner FROM Inventory "
            "WHERE title LIKE ? OR category LIKE ? OR owner LIKE ? ORDER BY id",
            (q, q, q)
        )
    else:
        cur.execute("SELECT id, title, category, item_count, owner FROM Inventory ORDER BY id")
    rows = cur.fetchall()
    conn.close()
    return rows

def add_inventory(title, category, item_count, owner):
    conn = get_db()
    cur = conn.cursor()
    cur.execute(
        "INSERT INTO Inventory (title, category, item_count, owner, created_at) VALUES (?, ?, ?, ?, ?)",
        (title, category, item_count, owner, datetime.now().strftime('%Y-%m-%d'))
    )
    conn.commit()
    conn.close()

def delete_inventory(inventory_id):
    conn = get_db()
    cur = conn.cursor()
    cur.execute("DELETE FROM Inventory WHERE id = ?", (inventory_id,))
    conn.commit()
    conn.close()

def update_inventory(inventory_id, title, category, item_count, owner):
    conn = get_db()
    cur = conn.cursor()
    cur.execute(
        "UPDATE Inventory SET title = ?, category = ?, item_count = ?, owner = ? WHERE id = ?",
        (title, category, item_count, owner, inventory_id)
    )
    conn.commit()
    conn.close()

@app.route('/', methods=['GET'])
def index():
    if not session.get('logged_in'):
        return redirect(url_for('login'))

    search_query = request.args.get('q', '')
    inventories = get_inventories(search_query)

    html = """
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Inventory Manager</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<style>
body {color:#0b2f26;font-family:"Inter",system-ui,sans-serif;background:#f8fafc;margin:0}
header {background:#0f5d4a;color:white;position:sticky;top:0;z-index:1000;box-shadow:0 4px 12px rgba(0,0,0,.1)}
.nav-container {max-width:1400px;margin:0 auto;padding:0 1.5rem;display:flex;align-items:center;justify-content:space-between;height:80px}
.logo {font-size:1.8rem;font-weight:800;letter-spacing:-1px}
.search-bar {width:100%;max-width:420px;height:48px;padding:0 1.2rem;border-radius:24px;border:none;background:rgba(255,255,255,.15);color:white;font-size:1rem}
.search-bar::placeholder {color:rgba(255,255,255,.7)}
.btn-green {background:#187964;color:white;padding:12px 28px;border-radius:16px;font-weight:600;transition:all .3s}
.btn-green:hover {background:#0f5d4a;transform:translateY(-2px)}
.btn-outline {border:2px solid white;color:white;padding:12px 28px;border-radius:16px;font-weight:600;transition:all .3s}
.btn-outline:hover {background:white;color:#0f5d4a}
.table-inv thead th {background:#f0f9f6;color:#0f5d4a;font-weight:700}
tr.new-row {animation:fadeIn 0.8s}
@keyframes fadeIn {from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
</style>
</head>
<body>
<?php include "header.php";?>
<div class="modal fade" id="newModal" tabindex="-1">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content rounded-3xl">
<div class="modal-header bg-green-50"><h5 class="modal-title">New Inventory</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body px-5 py-4">
<form id="newForm">
<div class="mb-3"><input type="text" class="form-control" id="title" placeholder="Title" required></div>
<div class="mb-3">
<select class="form-select" id="category" required>
<option value="Equipment">Equipment</option>
<option value="Book">Book</option>
<option value="Furniture">Furniture</option>
<option value="Other">Other</option>
</select>
</div>
<div class="mb-3"><input type="number" class="form-control" id="item_count" min="0" value="0" required></div>
<div class="mb-3"><input type="text" class="form-control" id="owner" value="Amina" required></div>
<button type="submit" class="btn-green w-100 py-3">Create</button>
</form>
</div>
</div>
</div>
</div>

<section class="bg-green-50 px-6 py-16">
<div class="max-w-6xl mx-auto">
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
<h3 class="m-0">Your Inventories</h3>
<div id="tableToolbar" class="d-none">
<button class="btn btn-sm btn-outline-primary me-2" id="editSelected">Edit Selected</button>
<button class="btn btn-sm btn-danger" id="deleteSelected">Delete Selected</button>
</div>
</div>

<div class="bg-white rounded-2xl shadow overflow-hidden border border-green-100">
<table class="table-inv w-full" id="inventoryTable">
<thead>
<tr>
<th class="py-4 px-6 text-center"><input type="checkbox" id="selectAll"></th>
<th class="py-4 px-6">Title</th>
<th class="py-4 px-6">Category</th>
<th class="py-4 px-6">Items</th>
<th class="py-4 px-6">Owner</th>
</tr>
</thead>
<tbody>
{% if inventories %}
{% for inv in inventories %}
<tr data-id="{{ inv['id'] }}">
<td class="py-4 px-6 text-center"><input type="checkbox" class="row-checkbox"></td>
<td class="py-4 px-6">{{ inv['title'] }}</td>
<td class="py-4 px-6">{{ inv['category'] }}</td>
<td class="py-4 px-6">{{ inv['item_count'] }}</td>
<td class="py-4 px-6">{{ inv['owner'] }}</td>
</tr>
{% endfor %}
{% else %}
<tr><td colspan="5" class="py-10 text-center text-gray-600">No inventories yet</td></tr>
{% endif %}
</tbody>
</table>
</div>
</div>
</section>

<script>
function updateToolbar(){
const count=document.querySelectorAll('.row-checkbox:checked').length;
document.getElementById('tableToolbar').classList.toggle('d-none',count===0);
document.getElementById('selectAll').checked=document.querySelectorAll('.row-checkbox').length===count&&count>0;
}

document.getElementById('selectAll')?.addEventListener('change',e=>{
document.querySelectorAll('.row-checkbox').forEach(chk=>chk.checked=e.target.checked);
updateToolbar();
});

document.querySelectorAll('.row-checkbox').forEach(chk=>{
chk.addEventListener('change',updateToolbar);
});

document.getElementById('newForm')?.addEventListener('submit',e=>{
e.preventDefault();
const data={
title:document.getElementById('title').value,
category:document.getElementById('category').value,
item_count:document.getElementById('item_count').value,
owner:document.getElementById('owner').value
};
fetch('/add_inventory',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(data)})
.then(r=>r.json()).then(res=>{if(res.success)location.reload()});
});

document.getElementById('deleteSelected')?.addEventListener('click',()=>{
if(!confirm('Delete selected inventories?'))return;
const ids=Array.from(document.querySelectorAll('.row-checkbox:checked'))
.map(chk=>chk.closest('tr').dataset.id);
fetch('/delete_batch',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({ids})})
.then(()=>location.reload());
});

document.getElementById('editSelected')?.addEventListener('click',()=>{
const checked=document.querySelector('.row-checkbox:checked');
if(!checked)return alert('Select one inventory to edit');
const row=checked.closest('tr');
const id=row.dataset.id;
const title=row.children[2].textContent;
const category=row.children[3].textContent;
const count=row.children[4].textContent;
const owner=row.children[5].textContent;

const newTitle=prompt('Edit Title:',title);
if(newTitle===null)return;
const newCategory=prompt('Edit Category:',category)||category;
const newCount=prompt('Edit Item Count:',count)||count;
const newOwner=prompt('Edit Owner:',owner)||owner;

fetch(`/update_inventory/${id}`,{
method:'PUT',
headers:{'Content-Type':'application/json'},
body:JSON.stringify({title:newTitle,category:newCategory,item_count:newCount,owner:newOwner})
}).then(()=>location.reload());
});
</script>
</body>
</html>
    """
    return render_template_string(html, inventories=inventories, search_query=search_query)

@app.route('/login', methods=['GET', 'POST'])
def login():
    if request.method == 'POST':
        username = request.form.get('username')
        password = request.form.get('password')
        if username in USERS and USERS[username] == password:
            session['logged_in'] = True
            return redirect(url_for('index'))
    return """
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <h2 class="text-center mb-4">Login</h2>
                        <form method="post">
                            <div class="mb-3"><input name="username" class="form-control" placeholder="Username" required autofocus></div>
                            <div class="mb-4"><input type="password" name="password" class="form-control" placeholder="Password" required></div>
                            <button type="submit" class="btn btn-success w-100">Login</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    """

@app.route('/logout')
def logout():
    session.pop('logged_in', None)
    return redirect(url_for('login'))

@app.route('/add_inventory', methods=['POST'])
def add_api():
    data = request.json
    add_inventory(data['title'], data['category'], int(data['item_count']), data['owner'])
    return jsonify({'success': True})

@app.route('/delete_batch', methods=['POST'])
def delete_batch():
    data = request.json
    for inventory_id in data.get('ids', []):
        delete_inventory(int(inventory_id))
    return jsonify({'success': True})

@app.route('/update_inventory/<int:id>', methods=['PUT'])
def update_api(id):
    data = request.json
    update_inventory(id, data['title'], data['category'], int(data['item_count']), data['owner'])
    return jsonify({'success': True})

if __name__ == '__main__':
    if not os.path.exists(DATABASE):
        conn = sqlite3.connect(DATABASE)
        conn.execute("""
        CREATE TABLE Inventory (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            category TEXT NOT NULL,
            item_count INTEGER DEFAULT 0,
            owner TEXT NOT NULL,
            created_at TEXT
        )
        """)
        conn.close()
    app.run(debug=True, port=5000)
