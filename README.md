# Practicle free version

Welcome! This is the repository for the free release of Practicle.

Practicle is an ITIL-based web system designed for managing inquiries, orders, changes, projects, tasks, CMDB (Configuration Management Database), and much more.

Read this first

This software is provided free of charge and without built in limitations. You are not allowed to sell this software, modify it, or misuse it in any way. Use of this software is at your own discretion, and Practicle is not liable for any data loss or errors arising from its use. We do not have or provide any support for this free version.

To enhance the software and better understand user needs, practicle will regularly transmit the following statistics:

- Number of active users
- Number of active companies
- Number of active teams
- Number of active groups
- Number of active elements
- Number of active projects
- Number of active assets types (cmdb)
- Number of active assets (cmdb)

Your informations provided in this installer will be registered. Please keep user information anonymous if you prefer that. No further personal or sensitive information is collected, transmitted or registered.

A Personal Note: I have realized that this project is too large for one person to handle alone. Therefore:

Looking for Collaborators: If you're interested in contributing to this project, partnering up, or reporting security issues, please don’t hesitate to contact me.

Show Your Support: If you use Practicle regularly, I’d greatly appreciate a recommendation on LinkedIn or Facebook.

Hobby Project Warning: Practicle is a hobby project and may gain commercial traction in the future. Please use it at your own risk. I recommend running this software on a local network.

Bug Reports and Feature Requests: If you encounter a bug or have a feature request, please contact me through the contact form on www.practicle.dk or email me at claus@practicle.dk. Include if possible:

- Screenshots of the error
- Error messages from the errorlog table

Installation guide

This installation guide is designed for installation on its own dedicated server so please dont install on a shared server.

1. install Ubuntu 24.04 (installation script will install all necessary requirements
2. go to user home folder
3. wget https://downloads.practicle.dk/installPracticle.sh .
4. chmod +x installPracticle.sh
5. run ./installPracticle.sh
6. installer will automatically install local mysql server, create admin user and install web files in /var/www/html/practicle
7. install certifiate for https (practicle installs with use of php-fpm)
8. add ssl configuration to the genereated apache config file
9. go to website and run https://systemname.local/install.php file
10. practicle will setup database and more, please note the user credentials the installer provides
11. If run successfully delete /var/www/html/practicle/install.php file and you can login with your provided credentials
