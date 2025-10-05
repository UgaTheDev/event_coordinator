# Event Coordinator

A full-stack web application for streamlined event planning and coordination, featuring intelligent scheduling, weather integration, and collaborative availability tracking.

![PHP](https://img.shields.io/badge/PHP-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=flat&logo=mysql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=flat&logo=javascript&logoColor=black)

## 🎯 Overview

Event Coordinator simplifies the process of organizing group events by providing tools for scheduling, RSVP management, and automated weather alerts. The platform uses a smart scheduling algorithm to find optimal meeting times based on participant availability.

## ✨ Key Features

### Event Management
- **Create & Manage Events** - Intuitive interface for event creation, editing, and deletion
- **RSVP System** - Seamless event registration and unregistration
- **Calendar View** - Visual display of all registered upcoming events

### Smart Scheduling
- **Availability Tracker** - Participants can input their availability windows
- **Optimal Time Calculator** - Algorithm analyzes availability matrices to suggest the best meeting times for all participants

### Weather Integration
- **Automated Weather Alerts** - Integration with Hong Kong Observatory API to flag events on:
  - Days with rain forecasts
  - Extreme temperature conditions (hot/cold warnings)
- *Note: Weather data is specific to Hong Kong region*

### Security
- **User Authentication** - Secure login and registration system with Google OAuth 2.0 support
- **Session Management** - Protected routes and persistent user sessions

## 🛠️ Tech Stack

**Frontend**
- HTML5, CSS3, JavaScript
- Responsive design for mobile and desktop

**Backend**
- PHP 7.4+
- RESTful architecture

**Database**
- MySQL 8.0
- Relational schema for users, events, and availability data

**Server**
- Apache (via XAMPP)
- PHP-supported web server

## License
This project is licensed under the MIT License.
