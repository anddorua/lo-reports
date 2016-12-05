FROM webdevops/php-nginx
ENV DEBIAN_FRONTEND noninteractive
ENV REPORT_DIR /home/application/reports
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
    && apt-get -q -y remove libreoffice-gnome
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
COPY prod/basic/ /root/.config/libreoffice/4/user/basic/
COPY prod/basic/ /home/application/.config/libreoffice/4/user/basic/
COPY prod/reports/ $REPORT_DIR