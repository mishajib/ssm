## Student Session Management

#### Installation and Setup

- Clone the repo
- Copy `.env.example` to `.env`
- Run `composer install`
- Create a database and update `.env` file with database credentials
- Run `php artisan migrate --seed`
- Update `APP_URL` in `.env` by hosted domain or localhost
- Run `php artisan serve`
- Visit `http://localhost:8000` in your browser

#### Features

- User Authentication (Only login and single user)
    - Login with email and password
- Student Management
    - List students
    - Create student
    - Manage student weekday availability
- Session Management
    - Schedule session with student for available weekdays
- Parse MS-Docx Files for extract and store the target improvement data for students
- Generate reports
    - Export reports in PDF format
    - Split PDF exports according to the session durations

#### API Endpoints

##### Auth Endpoints

- `POST /api/v1/login` - Login
- `GET /api/v1/user` - Logged In User
- `POST /api/v1/logout` - Logout

##### Student Endpoints

- `GET /api/v1/students` - List Students
- `POST /api/v1/students` - Create Student
- `GET /api/v1/students/{id}` - Show Student
- `PUT /api/v1/students/{id}` - Update Student
- `DELETE /api/v1/students/{id}` - Delete Student

#### Login Credentials

##### Teacher Login

- Email: `teacher@app.com`
- Password: `password`

##### Student Login

- Email: `student@app.com`
- Password: `password`

<h1 align="center">
** Thank you **
</h1>
