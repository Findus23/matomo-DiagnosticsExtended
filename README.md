# Matomo DiagnosticExtended Plugin

[![Translation status](https://hosted.weblate.org/widgets/matomo/-/communityplugin-diagnosticsextended/svg-badge.svg)](https://hosted.weblate.org/projects/matomo/communityplugin-diagnosticsextended/)

## Description

This plugin adds a collection of new checks to the Matomo System Check page. They are a bit **more experimental** and can be more likely to give incorrect results, but might help you find issues with your Matomo setup.

Please report back any unexpected results you come across or other feedback so that the checks can be improved and possible integrated into Matomo core once they work reliably.

### Currently supported tests:

- curl version check
- MySQL/MariaDB version check
- matomo.js check (Gzip, MIME-Type)
- Opcache check (enabled and set up correctly)
- php.ini settings
- php running as root
- php version check
- check if secret files are protected by webserver
