package utils

import (
	"encoding/base64"
	"net/smtp"
)

const (
	smtpHost     = "smtp.gmail.com"
	smtpPort     = "587"
	smtpFrom     = "projet.annuel42@gmail.com"
	smtpPassword = "wtwfapeaaevsowpr"
)

func SendEmail(to, subject, body string) error {
	auth := smtp.PlainAuth("", smtpFrom, smtpPassword, smtpHost)

	msg := []byte("To: " + to + "\r\n" +
		"Subject: " + subject + "\r\n" +
		"\r\n" +
		body + "\r\n")

	return smtp.SendMail(smtpHost+":"+smtpPort, auth, smtpFrom, []string{to}, msg)
}

func SendEmailWithAttachment(to, subject, body, filename string, attachment []byte) error {
	auth := smtp.PlainAuth("", smtpFrom, smtpPassword, smtpHost)

	boundary := "NOMOREWASTE-BOUNDARY"
	msg := "To: " + to + "\r\n" +
		"Subject: " + subject + "\r\n" +
		"MIME-Version: 1.0\r\n" +
		"Content-Type: multipart/mixed; boundary=\"" + boundary + "\"\r\n" +
		"\r\n" +
		"--" + boundary + "\r\n" +
		"Content-Type: text/plain; charset=\"utf-8\"\r\n" +
		"\r\n" +
		body + "\r\n" +
		"\r\n" +
		"--" + boundary + "\r\n" +
		"Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet\r\n" +
		"Content-Transfer-Encoding: base64\r\n" +
		"Content-Disposition: attachment; filename=\"" + filename + "\"\r\n" +
		"\r\n" +
		base64.StdEncoding.EncodeToString(attachment) + "\r\n" +
		"--" + boundary + "--\r\n"

	return smtp.SendMail(smtpHost+":"+smtpPort, auth, smtpFrom, []string{to}, []byte(msg))
}
