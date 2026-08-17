package utils

import "net/smtp"

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
