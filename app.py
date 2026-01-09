from flask import Flask, render_template_string
import sqlite3
from datetime import datetime

app = Flask(__name__)
app.secret_key = 'secret_key' #change when you are using the code
DATABASE = 'inventory.db'

def get_inventories():
    conn = sqlite3.connect(DATABASE)
    conn.row_factory = sqlite3.Row
    cur = conn.cursor()
    cur.execute("SELECT title, category, item_count, owner FROM Inventory ORDER BY id")
    rows = cur.fetchall()
    conn.close()
    return rows

@app.route('/')
def index():
    inventories = get_inventories()
    html_template = """<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Inventory Manager</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<script src="https://cdn.tailwindcss.com"></script>
<style>
body{color:#0b2f26;font-family:"Inter",system-ui,-apple-system,sans-serif;background:#f8fafc}
.hero{height:700px;background-image:url('https://thumbs.dreamstime.com/b/modern-laptop-analytics-dashboard-well-designed-office-space-showing-business-stylish-natural-lighting-green-plants-358652183.jpg');background-position:center bottom;background-size:cover;background-repeat:no-repeat;position:relative}
.hero-overlay{position:absolute;inset:0;background:rgba(0,0,0,.55);z-index:1}
.hero-content{position:relative;z-index:2}
h2{font-size:3.5rem;font-weight:800;line-height:1.1;letter-spacing:-1px}
h3{font-size:2.5rem;font-weight:800}
h4{font-size:1.5rem;font-weight:700}
p{font-size:1.125rem;line-height:1.7}
.btn-green{background:#0f5d4a;color:#fff;padding:14px 32px;border-radius:16px;font-weight:600;transition:all .3s}
.btn-green:hover{background:#187964;transform:translateY(-2px)}
.card-nf{background:#fff;border-radius:22px;padding:32px;border:1px solid #daebe3;transition:all .3s}
.card-nf:hover{transform:translateY(-8px);box-shadow:0 20px 40px rgba(15,93,74,.12)}
.table-inv thead th{background:#f0f9f6;color:#0f5d4a;font-weight:700}
.stats-number{font-size:4rem;font-weight:800;color:#0f5d4a}
</style>
</head>
<body>
<?php include "header.php";?>
<section class="hero w-full flex items-center justify-center text-center">
<div class="hero-overlay"></div>
<div class="hero-content relative z-10 px-6 max-w-5xl">
<h2 class="text-white mb-6 drop-shadow-lg">Smart Inventory Management</h2>
<p class="text-white text-xl mb-10 drop-shadow-md max-w-3xl mx-auto">Create, track and manage any inventory — equipment, books, documents — with custom fields and unique IDs.</p>
<div class="flex flex-wrap gap-5 justify-center">
<a href="#"><button class="btn-green">Create New Inventory</button></a>
<a href="#"><button class="btn-green bg-transparent border-2 border-white hover:bg-white hover:text-green-900">Explore Public Inventories</button></a>
</div>
</div>
</section>
<section class="bg-green-50 px-6 py-20 lg:px-20">
<div class="max-w-6xl mx-auto text-center">
<h3 class="mb-16">Why Choose Inventory Manager?</h3>
<div class="grid grid-cols-1 md:grid-cols-4 gap-8">
<div class="card-nf"><div class="text-green-700 text-5xl mb-6"><i class="fas fa-boxes-stacked"></i></div><h4>Flexible Structure</h4><p class="mt-4 text-gray-700">Custom fields & ID formats for any type of inventory.</p></div>
<div class="card-nf"><div class="text-green-700 text-5xl mb-6"><i class="fas fa-users"></i></div><h4>Team Access</h4><p class="mt-4 text-gray-700">Granular permissions — public, private or selected users.</p></div>
<div class="card-nf"><div class="text-green-700 text-5xl mb-6"><i class="fas fa-chart-line"></i></div><h4>Real-time Stats</h4><p class="mt-4 text-gray-700">Counts, averages, trends — always up to date.</p></div>
<div class="card-nf"><div class="text-green-700 text-5xl mb-6"><i class="fas fa-lock"></i></div><h4>Secure & Simple</h4><p class="mt-4 text-gray-700">Local-first, easy to host, full control.</p></div>
</div>
</div>
</section>
<section class="bg-white px-6 py-20 lg:px-20">
<div class="max-w-6xl mx-auto text-center">
<h3 class="mb-16">Platform at a Glance</h3>
<div class="grid grid-cols-2 md:grid-cols-4 gap-12">
<div><p class="stats-number">148</p><p class="text-lg text-gray-700 mt-2">Inventories</p></div>
<div><p class="stats-number">4.7k</p><p class="text-lg text-gray-700 mt-2">Items Tracked</p></div>
<div><p class="stats-number">24/7</p><p class="text-lg text-gray-700 mt-2">Availability</p></div>
<div><p class="stats-number">5+</p><p class="text-lg text-gray-700 mt-2">Categories</p></div>
</div>
</div>
</section>
<section class="bg-green-50 px-6 py-20 lg:px-20">
<div class="max-w-6xl mx-auto">
<div class="flex flex-col sm:flex-row justify-between items-center mb-10">
<h3 class="text-center sm:text-left">Your Inventories</h3>
<button class="btn-green mt-4 sm:mt-0">+ Add New Inventory</button>
</div>
<div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-green-100">
<table class="table-inv w-full">
<thead><tr><th class="py-5 px-8 text-left">Title</th><th class="py-5 px-8 text-left">Category</th><th class="py-5 px-8 text-left">Items</th><th class="py-5 px-8 text-left">Owner</th></tr></thead>
<tbody>
{% if inventories %}
{% for inv in inventories %}
<tr class="border-t border-green-100 hover:bg-green-50 transition">
<td class="py-6 px-8 font-medium">{{ inv['title'] }}</td>
<td class="py-6 px-8 text-gray-700">{{ inv['category'] }}</td>
<td class="py-6 px-8 text-gray-700">{{ inv['item_count'] }}</td>
<td class="py-6 px-8 text-gray-700">{{ inv['owner'] }}</td>
</tr>
{% endfor %}
{% else %}
<tr><td colspan="4" class="py-20 text-center text-gray-600">No inventories yet. Create your first one!</td></tr>
{% endif %}
</tbody>
</table>
</div>
</div>
</section>
<footer class="bg-green-900 text-white py-12 text-center">
<p class="text-lg">© {{ current_year }} Inventory Manager • Pavlodar, Kazakhstan</p>
</footer>
</body>
</html>"""
    return render_template_string(html_template, inventories=inventories, current_year=datetime.now().year)

if __name__ == '__main__':
    app.run(debug=True, port=5000)
