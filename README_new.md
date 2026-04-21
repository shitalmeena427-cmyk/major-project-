# Smart Quiz & Performance Analysis System

A comprehensive full-stack web application for online mock tests, featuring admin quiz management, student performance analysis, advanced analytics, AI-based suggestions, ranking, and notifications.

## 🧩 Advanced Features Implemented

### Admin Panel
- Secure login system with role-based access
- Create, edit, delete quizzes with status (active/inactive)
- Add/edit/delete questions dynamically
- Assign subject/category to quizzes
- Manage quiz activation/deactivation
- View all student attempts with detailed results
- Dashboard showing total quizzes, students, average performance, most difficult questions

### Student Panel
- Register/Login with secure authentication
- View active quizzes by subject/category
- Attempt timed quizzes (5-min timer) with auto-submit
- Prevent refresh/back navigation during quiz
- Instant result display with detailed analysis
- Question-wise review (correct vs selected answers)
- Rank among all students and class average
- Personal quiz history with subject/date filters
- Advanced analytics dashboard with Chart.js:
  - Score trend over time (line chart)
  - Subject-wise performance (bar chart)
  - Best score, average score, improvement %
- AI-based suggestions for weak subjects
- Leaderboard with top performers and user ranking
- Notification system for new quizzes/rank improvements

### Additional Features
- AJAX/Fetch API for dynamic interactions (no page reloads)
- Responsive design (mobile-friendly)
- Performance optimization with efficient queries
- Clean MVC-inspired architecture
- Secure input validation and SQL injection protection
- Session-based authentication

## 🗄️ Enhanced Database Structure

- **users**: id, name, email, password, role
- **quizzes**: id, title, subject, status
- **questions**: id, quiz_id, question, option1-4, correct_option
- **results**: id, user_id, quiz_id, score, percentage, attempt_date
- **answers**: id, user_id, question_id, selected_option
- **notifications**: id, user_id, message, read_status, created_at

## 🚀 Setup Instructions

### Prerequisites
- XAMPP (Apache, MySQL, PHP)
- Web browser

### Installation Steps

1. **Start XAMPP**
   - Launch XAMPP Control Panel
   - Start Apache and MySQL

2. **Database Setup**
   - Go to `http://localhost/phpmyadmin/`
   - Create database `quiz_system`
   - Import `db/quiz_system.sql`

3. **Deploy Application**
   - Copy project to `C:\xampp\htdocs\smart-quiz-system`
   - Access at `http://localhost/smart-quiz-system/`

### Default Credentials
- **Admin**: admin@quiz.com / admin123
- **Student**: Register new account

## 📁 Project Structure

```
smart-quiz-system/
├── index.php                 # Landing page
├── admin/                    # Admin panel
│   ├── login.php
│   ├── dashboard.php
│   ├── create_quiz.php
│   ├── add_questions.php
│   ├── manage_quizzes.php
│   └── view_results.php
├── student/                  # Student panel
│   ├── register.php
│   ├── login.php
│   ├── dashboard.php
│   ├── take_quiz.php
│   ├── process_quiz.php
│   ├── result.php
│   ├── history.php
│   ├── analytics.php
│   └── leaderboard.php
├── includes/                 # Core files
│   ├── db.php               # Database connection
│   └── functions.php       # Helper functions
├── css/
│   └── style.css           # Responsive styles
├── js/
│   └── script.js           # Timer and interactions
├── db/
│   └── quiz_system.sql     # Database schema
└── README.md
```

## 🔐 Security Features

- Password hashing (PHP password_hash)
- Prepared statements (PDO)
- Input sanitization
- Session management
- Role-based access control

## 🎯 Usage Guide

### For Students
1. Register/Login
2. View available quizzes
3. Take timed quiz
4. View instant results and analysis
5. Check analytics, history, leaderboard
6. Receive notifications

### For Admins
1. Login to admin panel
2. Create/manage quizzes and questions
3. Activate/deactivate quizzes
4. Monitor student performance
5. View detailed results

## 🛠️ Technologies Used

- **Backend**: PHP 8+ with PDO
- **Frontend**: HTML5, CSS3, JavaScript (ES6)
- **Database**: MySQL
- **Charts**: Chart.js
- **Styling**: Responsive CSS

## 📈 Analytics & AI Features

- **Score Trend**: Line chart showing performance over time
- **Subject Analysis**: Bar chart for subject-wise scores
- **AI Suggestions**: Automatic detection of weak areas
- **Ranking System**: Global leaderboard with user position
- **Notifications**: Real-time alerts for new content

## 📝 Notes

- Timer set to 5 minutes (configurable in js/script.js)
- Database uses port 3307 (update includes/db.php if different)
- All passwords are hashed for security
- Application is scalable and production-ready

## 🤝 Contributing

This is a complete, professional quiz system suitable for educational institutions. Feel free to enhance further!

## 📄 License

Open-source project for educational purposes.