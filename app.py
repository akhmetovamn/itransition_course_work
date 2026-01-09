from flask import Flask, render_template_string
import sqlite3
from datetime import datetime

app = Flask(__name__)

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
    
    html_template = """
<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-100">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inventory Manager</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body class="h-full flex flex-col">

  <header class="bg-white shadow-sm">
    <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
      <h1 class="text-xl font-bold">Inventory Manager</h1>
      <div class="flex-1 max-w-md mx-4">
        <div class="relative">
          <input type="search" placeholder="Search inventories, items..." 
                 class="w-full pl-9 pr-3 py-2 border rounded-md bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
          <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
        </div>
      </div>
      <div class="flex items-center gap-3">
        <span class="text-gray-700 font-medium">Hello, Amina</span>
        <a href="#" class="text-gray-600 hover:text-gray-900">Logout</a>
      </div>
    </div>
  </header>

  <main class="flex-1 max-w-7xl mx-auto w-full px-4 py-6">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-xl font-bold">All Inventories</h2>
      <button class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 flex items-center gap-2">
        <i class="fas fa-plus"></i> New Inventory
      </button>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Owner</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          {% if inventories %}
            {% for inv in inventories %}
            <tr class="hover:bg-gray-50 cursor-pointer">
              <td class="px-4 py-3 text-sm font-medium">{{ inv['title'] }}</td>
              <td class="px-4 py-3 text-sm text-gray-500">{{ inv['category'] }}</td>
              <td class="px-4 py-3 text-sm text-gray-500">{{ inv['item_count'] }}</td>
              <td class="px-4 py-3 text-sm text-gray-500">{{ inv['owner'] }}</td>
            </tr>
            {% endfor %}
          {% else %}
            <tr>
              <td colspan="4" class="px-4 py-8 text-center text-gray-500">No inventories found.</td>
            </tr>
          {% endif %}
        </tbody>
      </table>
    </div>

    <div class="mt-12">
      <h3 class="text-lg font-semibold mb-6 text-center">Popular Use Cases</h3>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
        <div class="text-center">
          <img src="https://www.shutterstock.com/image-photo/modern-laptop-showcasing-data-reports-260nw-2695314115.jpg" alt="Office Equipment" class="mx-auto rounded-lg shadow-md object-cover h-48 w-full">
          <p class="mt-3 font-medium">Office Equipment</p>
        </div>
        <div class="text-center">
          <img src="https://dontyoushushme.com/wp-content/uploads/2022/02/20180316_131943508_ios.jpg" alt="Library Books" class="mx-auto rounded-lg shadow-md object-cover h-48 w-full">
          <p class="mt-3 font-medium">Library Books</p>
        </div>
        <div class="text-center">
          <img src="https://images2.imgix.net/p4dbimg/751/images/23538-1.jpg?fit=fill&bg=FFFFFF&trim=color&trimtol=15&trimcolor=FFFFFF&w=1024&h=768&fm=pjpg&auto=compress" alt="Office Furniture" class="mx-auto rounded-lg shadow-md object-cover h-48 w-full">
          <p class="mt-3 font-medium">Office Furniture</p>
        </div>
        <div class="text-center">
          <img src="https://www.laserfiche.com/wp-content/uploads/2023/04/transparent-records-management.png" alt="HR Documents" class="mx-auto rounded-lg shadow-md object-cover h-48 w-full">
          <p class="mt-3 font-medium">HR Documents</p>
        </div>
      </div>
    </div>
  </main>

  <footer class="bg-white border-t py-4 mt-auto">
    <div class="max-w-7xl mx-auto px-4 text-center text-sm text-gray-600">
      © 2026 Inventory Manager • Pavlodar, Kazakhstan
    </div>
  </footer>

</body>
</html>
    """
    
    return render_template_string(
        html_template,
        inventories=inventories
    )

if __name__ == '__main__':
    app.run(debug=True, port=5000)
