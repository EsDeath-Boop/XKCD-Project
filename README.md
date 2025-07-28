# XKCD Comic Email Sender

A PHP-based project that automatically fetches the latest [XKCD](https://xkcd.com) comic and sends it to a list of subscribers via email. The script is designed to run as a scheduled task (CRON job) and delivers HTML-formatted emails.

## Features

- Fetches the latest XKCD comic image and title.
- Sends HTML-formatted emails to all subscribers.
- Maintains a simple subscription list using a text file.
- Automates sending using CRON jobs.
- Lightweight and easy to deploy.

## Requirements

- PHP 7.4+
- cURL enabled
- Access to a mail server or SMTP configuration
- CRON access (for automated sending)

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/<your-username>/xkcd-email-sender.git
   cd xkcd-email-sender
