# itransition_course_work running locally
```bash
# 1. Clone the repository
git clone https://github.com/akhmetovamn/itransition_course_work.git
cd inventory-manager

# 2. Install dependencies
pip install -r requirements.txt

# 3. Create database
sqlite3 inventory.db < schema.sql

# 4. Run the application
python app.py
