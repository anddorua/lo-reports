FROM webdevops/php-nginx
ENV DEBIAN_FRONTEND noninteractive
ENV REPORT_DIR /home/application/reports
ENV REPORT_DONE_DIR /home/application/reports-done
ENV LOG_LEVEL INFO
# version 1:4.2.8-0ubuntu2

RUN apt-get update \
    && apt-get -y -q install \
	libreoffice \
	libreoffice-writer \
	libreoffice-calc \
	ure \
	libreoffice-java-common \
	libreoffice-core \
	libreoffice-common \
	openjdk-8-jre \
	fonts-opensymbol \
	hyphen-fr hyphen-de hyphen-en-us hyphen-it hyphen-ru \
	fonts-dejavu fonts-dejavu-core fonts-dejavu-extra fonts-noto \
	fonts-dustin fonts-f500 fonts-fanwood fonts-freefont-ttf \
	fonts-liberation fonts-lmodern fonts-lyx fonts-sil-gentium \
	fonts-texgyre fonts-tlwg-purisa \
    && apt-get -q -y remove libreoffice-gnome \
    && mkdir $REPORT_DONE_DIR \
    && chmod 777 $REPORT_DONE_DIR
EXPOSE 8997
#    && apt-get -q -y remove libreoffice-gnome # removed

RUN adduser --home=/opt/libreoffice --disabled-password --gecos "" --shell=/bin/bash libreoffice
# replace default setup with a one disabling logos by default
ADD sofficerc /etc/libreoffice/soffice
# init default script folders and content
RUN soffice --calc --headless --norestore "macro:///Standard.Module1.Main"
RUN runuser -l application -c 'soffice --calc --headless --norestore "macro:///Standard.Module1.Main"'
# VOLUME ["/tmp"]
# copy own scripts
COPY dev/basic/ /root/.config/libreoffice/4/user/basic/
COPY dev/basic/ /home/application/.config/libreoffice/4/user/basic/
COPY dev/reports/ $REPORT_DIR
COPY dev/site/ /app
COPY dev/nginx/vhost.common.d /opt/docker/etc/nginx/vhost.common.d
COPY dev/supervisor/conf/report-workers.conf /opt/docker/etc/supervisor.d/report-workers.conf
VOLUME $REPORT_DIR
VOLUME $REPORT_DONE_DIR