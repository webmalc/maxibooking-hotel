FROM solita/ubuntu-systemd:16.04

#RUN echo 'root:root' | chpasswd

#RUN DEBIAN_FRONTEND=noninteractive apt-get update
#RUN DEBIAN_FRONTEND=noninteractive apt-get install -y aptitude sudo openssh-server python

#RUN mkdir /var/run/sshd
#RUN sed -i 's/PermitRootLogin prohibit-password/PermitRootLogin yes/' /etc/ssh/sshd_config

#EXPOSE 8000 8000
#CMD ["/usr/sbin/sshd", "-D"]
