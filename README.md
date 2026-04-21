# Smart Quiz & Performance Analysis System

A comprehensive full-stack web application for online mock tests, featuring admin quiz management and student performance analysis.

## 🧩 Features

### Admin Panel
- Secure login system
- Create and manage quizzes with multiple questions
- Add 4 options per question with correct answer selection
- View all student results and statistics
- Dashboard with total quizzes, students, and average scores

### Student Panel
- User registration and login
- Browse available quizzes by subject
- Take timed quizzes with countdown timer
- Instant result display with detailed analysis
- Question-wise breakdown (correct vs selected answers)
- Rank among all students
- Class average score comparison
- Personal quiz history

### Additional Features
- 5-minute timer per quiz with auto-submit
- Prevent page refresh during quiz attempt
- Mobile-responsive design
- Secure authentication and input validation
- SQL injection protection
- Performance charts using Chart.js

## 🗄️ Database Structure

- **users**: id, name, email, password, role
- **quizzes**: id, title, subject
- **questions**: id, quiz_id, question, option1-4, correct_option
- **results**: id, user_id, quiz_id, score, percentage, attempt_date
- **answers**: id, user_id, question_id, selected_option

## 🚀 Setup Instructions

### Prerequisites
- XAMPP (or WAMP/LAMP) with PHP 7+ and MySQL
- Web browser

### Installation Steps

1. **Download and Install XAMPP**
   - Download from https://www.apachefriends.org/
   - Install and start Apache and MySQL modules

2. **Setup Project**
   - Clone or download this project
   - Place the entire folder in `C:\xampp\htdocs\` (Windows) or `/opt/lampp/htdocs/` (Linux)

3. **Database Setup**
   - Open phpMyAdmin at http://localhost/phpmyadmin/
   - Create a new database named `quiz_system`
   - Import the `db/quiz_system.sql` file

4. **Configure Database Connection** (if needed)
   - Edit `includes/db.php` for custom MySQL credentials
   - Default: host=localhost, user=root, password='' (XAMPP default)

5. **Access the Application**
   - Open browser and go to: http://localhost/smart-quiz-system/
   - Admin Login: admin@quiz.com / admin123
   - Student: Register new account or use existing

## 📁 Project Structure

```
smart-quiz-system/
├── index.php                 # Main landing page
├── admin/                    # Admin panel
│   ├── login.php
│   ├── dashboard.php
│   ├── create_quiz.php
│   ├── add_questions.php
│   └── view_results.php
├── student/                  # Student panel
│   ├── register.php
│   ├── login.php
│   ├── dashboard.php
│   ├── take_quiz.php
│   ├── process_quiz.php
│   ├── result.php
│   └── history.php
├── includes/                 # Common files
│   ├── db.php               # Database connection
│   └── functions.php       # Helper functions
├── css/
│   └── style.css           # Main stylesheet
├── js/
│   └── script.js           # JavaScript for timer and interactions
├── db/
│   └── quiz_system.sql     # Database schema and sample data
└── README.md
```

## 🔐 Security Features

- Password hashing using PHP's password_hash()
- Prepared statements to prevent SQL injection
- Input sanitization and validation
- Session-based authentication
- Role-based access control

## 🎯 Usage

1. **Admin**: Login to create quizzes and monitor results
2. **Student**: Register, login, take quizzes, view detailed analysis
3. **Timer**: Each quiz has a 5-minute limit
4. **Results**: Instant feedback with charts and rankings

## 🛠️ Technologies Used

- **Frontend**: HTML5, CSS3, JavaScript (ES6)
- **Backend**: PHP 7+
- **Database**: MySQL
- **Charts**: Chart.js
- **Styling**: Responsive CSS with Flexbox/Grid

## 📝 Notes

- Default admin password should be changed in production
- Timer duration can be modified in `js/script.js`
- Database credentials can be updated in `includes/db.php`
- Ensure file permissions allow PHP execution

## 🤝 Contributing

Feel free to fork and improve the system!

## 📄 License

This project is open-source. Use at your own risk.