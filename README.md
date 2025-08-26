# ScriptAI Laravel Backend

<div align="center">

![ScriptAI Backend](https://img.shields.io/badge/ScriptAI-Laravel%20Backend-red?style=for-the-badge&logo=laravel)

**Professional Laravel API for AI-powered Arabic script generation with OpenAI integration**

[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=flat&logo=laravel)](https://laravel.com/)
[![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?style=flat&logo=php)](https://php.net/)
[![OpenAI](https://img.shields.io/badge/OpenAI-GPT--4-412991?style=flat&logo=openai)](https://openai.com/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=flat&logo=mysql)](https://mysql.com/)

</div>

## ✨ Features

### 🤖 AI-Powered Script Generation
- **OpenAI GPT-4 Integration** with optimized prompts
- **5 Tone Options**: Enthusiastic (حماسي), Comedy (كوميدي), Educational (تعليمي), Storytelling (قصصي), Professional (احترافي)
- **Arabic & English Support** with proper RTL handling
- **Content Quality Scoring** based on engagement metrics
- **Smart Duration Estimation** for video planning

### 📊 Advanced Analytics
- **Real-time Statistics** on script generation
- **Popular Topics Tracking** and trend analysis
- **Tone Distribution Analytics** for content optimization
- **Performance Metrics** including quality and engagement scores
- **Daily Generation Reports** with historical data

### 🛡️ Production-Ready Features
- **Eloquent ORM Models** with relationships and scopes
- **Database Migrations** with proper indexing
- **Input Validation** and sanitization ready
- **Rate Limiting** configuration ready
- **CORS Support** for frontend integration
- **Environment Configuration** for different deployments

## 🚀 Quick Start

### Prerequisites
- PHP 8.1+
- Composer
- SQLite (or MySQL for production)
- OpenAI API Key

### Installation

1. **Clone the repository**
```bash
git clone https://github.com/SuhaibAlqulfaty/scriptai-backend.git
cd scriptai-backend
```

2. **Install dependencies**
```bash
composer install
```

3. **Environment setup**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configure environment variables**
```bash
# Edit .env file
OPENAI_API_KEY=your_openai_api_key_here
```

5. **Database setup**
```bash
touch database/database.sqlite
php artisan migrate
```

6. **Start the development server**
```bash
php artisan serve
```

## 📊 Database Schema

### Scripts Table
- Complete script generation tracking
- Quality and engagement scoring
- Multi-language support
- Performance analytics

### Script Feedback Table
- User rating system
- Detailed feedback metrics
- Quality improvement tracking

## 🔧 Environment Configuration

```bash
# OpenAI Integration
OPENAI_API_KEY=your_openai_api_key_here
OPENAI_MODEL=gpt-4

# ScriptAI Settings
SCRIPTAI_DEFAULT_TONE=educational
SCRIPTAI_SUPPORTED_LANGUAGES=ar,en
```

## 👨‍💻 Author

**Suhaib Alqulfaty**
- GitHub: [@SuhaibAlqulfaty](https://github.com/SuhaibAlqulfaty)

## 🔄 Next Steps

1. **Create OpenAI Service** for script generation
2. **Build API Controllers** with proper validation
3. **Implement Rate Limiting** middleware
4. **Add CORS Configuration** for frontend
5. **Create API Documentation** with examples

---

<div align="center">

**Built with ❤️ for the Arabic content creation community 🇸🇦**

If you find this project helpful, please give it a ⭐ on GitHub!

</div>
