# 🎙️ Speech Club Management System

A simple, modern web app to run speech clubs, inspired by Toastmasters. It helps clubs schedule meetings, assign roles, run live meetings with clicker tools, track member speeches, and get AI speaking tips.

---

## 🌟 What This App Does

### 🏢 Manage Multiple Clubs
- **Run several clubs**: Set up different clubs in your company or community (like Chola, Chera, Pandiya, Pallava).
- **Club switcher**: If you are an admin, switch between clubs easily from the top bar.
- **Club privacy**: Regular members only see information from their own club.
- **Club officers**: Assign club leadership roles (President, VP Education, Secretary, etc.) for each club.

### 📅 Plan and Run Meetings
- **Schedule meetings**: Add meeting numbers, dates, times, location, meeting themes, and a Word of the Day.
- **Meeting statuses**: Move meetings from `Draft` ➔ `Scheduled` ➔ `Completed` (or `Cancelled`).
- **Meeting lock**: Once a meeting is marked `Completed`, the data is locked so nobody can change it by accident.
- **Meeting roles**: Assign functionaries like TMOD (host), Timer, Grammarian, Ah-Counter, General Evaluator, and Listening Master.
- **Volunteer online**: Members can sign up for speech slots or Table Topics directly from the meeting page, or cancel if they cannot make it.
- **Speech projects**: Select speech projects from the built-in catalog to automatically fill in the project goals and time limits.

### ⚡ Live Meeting Tools (During the Meeting)
- **Live Ah-Counter**: Click `+` and `-` buttons live to count filler words (`ah`, `um`, `er`, `so`, `like`, `you know`, repeated words) for each speaker.
- **Live Grammarian**: Count how many times each speaker uses the Word of the Day, and jot down good phrases and grammar mistakes.
- **Live Timer**: Record speech times for speakers and evaluators. The system shows if they qualified within Green, Amber, and Red time limits.
- **Speech Evaluations**: Evaluators can type written feedback (what went well, recommendations for improvement).
- **Listening Master**: Test how well the audience listened by adding questions and notes at the end of the meeting.

### 📄 Download PDF Agendas & Reports
- **Meeting Agenda PDF**: Print a neat schedule with timings, speaker names, and role players.
- **Meeting Report PDF**: Download a full summary after the meeting, showing attendance, timer results, speech evaluations, and filler word counts.
- **7 Color Themes**: Pick your favorite color for the PDF (`indigo`, `emerald`, `blue`, `purple`, `rose`, `amber`, `cyan`).

### 📈 Member Progress & Profiles
- **Role Tracker (`/my-progress`)**: See which meeting roles you have completed and which roles you still need to try.
- **Speeches & Badges**: Track how many speeches you have delivered and unlock milestone badges.
- **Member Profile (`/members/{user}`)**: A full view of a member's speeches, evaluations given/received, attendance history, and AI coaching.

### 🤖 Built-in AI Assistant
- **Works offline**: Uses a built-in PHP rule engine by default — no API keys or setup required.
- **Local Ollama support (optional)**: Connect to a local model (like Gemma 4) for smarter answers.
- **5 Useful Modes**:
  1. **General Chat**: Ask questions about public speaking and club rules.
  2. **Speech Outlines**: Turn an idea or topic into a ready-to-use speech outline.
  3. **Table Topics**: Get fresh topics and questions for impromptu speaking sessions.
  4. **Role Scripts**: Generate what to say when introducing roles like Timer, Ah-Counter, or TMOD.
  5. **Member Coaching**: Get personalized advice based on a member's past speeches and filler words.
- **Quick AI Modal**: Click the AI button from any page for quick help.

### 🔐 User Roles & Permissions
- **Global Roles**: Super Admin, Admin, Global Viewer.
- **Club Roles**: President, VP Education, VP Membership, VP Public Relations, Secretary, Treasurer, Sergeant at Arms, Member.

---

## 🛠️ Tech Stack

| Part | What We Use |
|---|---|
| **Backend** | [Laravel 13](https://laravel.com), [PHP 8.3+](https://www.php.net) |
| **Frontend** | [Livewire 4](https://livewire.laravel.com), [Alpine.js](https://alpinejs.dev), [Tailwind CSS v4](https://tailwindcss.com) |
| **Database** | SQLite (default for development) or MySQL |
| **Permissions** | [Spatie Laravel-Permission](https://spatie.be/docs/laravel-permission) |
| **PDFs** | [Barryvdh Laravel-DomPDF](https://github.com/barryvdh/laravel-dompdf) |
| **AI Assistant** | [Ollama](https://ollama.ai) (optional) + built-in offline PHP engine |
| **Assets** | [Vite 7](https://vitejs.dev) |

---

## 📋 Requirements

Before you start, make sure you have:
- **PHP**: `8.3` or higher
- **Composer**: `2.x`
- **Node.js**: `20.x` or higher & **npm**
- **SQLite** (comes with PHP) or MySQL
- *(Optional)* **Ollama**: Only if you want to run AI models locally.

---

## 🚀 How to Set Up and Run

### 1. Clone the Project
```bash
git clone https://github.com/IamMeiy/speech-club-app.git
cd speech-club-app
```

### 2. Quick Setup (Option A)
Run the setup command to install everything, create your key, and prepare the database:
```bash
composer run setup
php artisan db:seed
```

---

### 3. Manual Setup (Option B)

If you prefer running steps one by one:

#### Step 1: Install packages
```bash
composer install
npm install
```

#### Step 2: Set up environment
```bash
# On Linux, macOS, or PowerShell:
cp .env.example .env

# On Windows Command Prompt (CMD):
copy .env.example .env

# Generate app key:
php artisan key:generate
```

#### Step 3: Set up database and demo data
```bash
# Create SQLite file if it does not exist:
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"

# Run migrations and seed sample clubs, users, and meetings:
php artisan migrate --seed
```

#### Step 4: Start the server
Run everything with one command:
```bash
composer run dev
```

Or run them in two separate terminal windows:
```bash
# Terminal 1:
php artisan serve

# Terminal 2:
npm run dev
```

Open your browser and visit: **`http://localhost:8000`**

---

## 👥 Demo Logins

The database comes pre-loaded with demo users. The password for **all accounts** is: **`password`**

| Email | Role | Club | What They Can Do |
|---|---|---|---|
| `superadmin@speechclub.local` | **Super Admin** | All Clubs | Full access to everything |
| `admin@speechclub.local` | **Admin** | Chola, Chera, Pandiya | Manage multiple clubs |
| `arun@speechclub.local` | **President** | Chola Club | Manage Chola club and meetings |
| `kumar@speechclub.local` | **VP Education** | Chola Club | Manage agendas, meetings, and roles |
| `priya@speechclub.local` | **Secretary** | Chola Club | Take attendance and minutes |
| `suresh@speechclub.local` | **Member** | Chola Club | Give speeches and take meeting roles |
| `meena@speechclub.local` | **Member** | Chola Club | Give speeches and take meeting roles |
| `ravi@speechclub.local` | **President** | Chera Club | Manage Chera club |
| `karthik@speechclub.local` | **Member** | Chera Club | Member in Chera club |

---

## 🤖 How to Use the AI Assistant

### Default (Offline, Zero Setup)
You do not need to install anything. The app has a built-in rule engine that writes speech outlines, Table Topics, and role scripts out of the box.

### Optional: Use Local Ollama
To use a real AI model on your machine:
1. Install [Ollama](https://ollama.ai).
2. Download the model:
   ```bash
   ollama pull gemma4:e4b
   ```
3. Make sure Ollama is running in the background.
4. Check your `.env` file has:
   ```env
   AI_ASSISTANT_ENABLED=true
   OLLAMA_URL=http://127.0.0.1:11434
   OLLAMA_MODEL=gemma4:e4b
   OLLAMA_TIMEOUT=180
   ```

---

## 🧪 Testing

Run the automated test suite anytime:
```bash
php artisan test
```

All 67 tests will run, testing permissions, club switching, meetings, live clickers, and PDF reports.

---

## 📁 Project Folders

```text
speech-club-app/
├── app/
│   ├── Livewire/             # Interactive web pages (Meetings, Clubs, AI, Members)
│   ├── Models/               # Database models (Club, Meeting, User, etc.)
│   └── Services/             # Core logic (AI assistant, Club access)
├── database/
│   ├── migrations/           # Database tables
│   └── seeders/              # Demo data generator
├── resources/
│   ├── css/                  # Styling (Tailwind CSS)
│   └── views/                # Page layouts and PDF templates
├── routes/
│   └── web.php               # Web page URLs
└── tests/                    # Automated tests
```

## 👤 Author

Created and maintained by **[Meiy](https://github.com/IamMeiy)**.

---

## 📄 License

This project is open-source under the [MIT license](LICENSE).

---

## ⚖️ Disclaimer

*Toastmasters*, *Toastmaster*, and related trademarks are registered trademarks of **Toastmasters International**. 

This repository and application are an independent, community open-source project. This project is **not affiliated with, endorsed by, sponsored by, or connected to Toastmasters International** in any official capacity. Any reference to Toastmasters or its meeting roles is made strictly for descriptive purposes under fair use.
