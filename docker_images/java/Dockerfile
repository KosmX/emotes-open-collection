FROM openjdk:17-jdk
ADD ./emote_format-cli.jar /usr/src/daemon/emote_format-cli.jar
WORKDIR /usr/src/daemon
#CMD ["java", "-cp", ".\emote_format-cli.jar dev.kosmx.emoteFormatCLI.SocketDaemonKt", "-p", "3000"]
CMD "java" "-cp" "./emote_format-cli.jar" "dev.kosmx.emoteFormatCLI.SocketDaemonKt" "-p" "3000"
