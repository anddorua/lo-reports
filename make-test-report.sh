#!/bin/bash
/usr/bin/libreoffice5.1 --headless --norestore --nodefault 'macro:///Standard.Starter.Report("/home/andriy/projects/lo-reports/dev/reports/report1.ods", "/home/andriy/projects/lo-reports/dev/reports/test2.xls", "/home/andriy/projects/lo-reports/dev/reports")'

# command to run in connected shell
# soffice --invisible --norestore --nodefault 'macro:///Standard.Starter.Report("/home/application/reports/report1.ods", "/home/application/reports/test2.xls", "/home/application/reports")'
