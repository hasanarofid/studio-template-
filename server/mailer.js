const nodemailer = require('nodemailer');
require('dotenv').config();

const transporter = nodemailer.createTransport({
  host: process.env.MAIL_HOST,
  port: parseInt(process.env.MAIL_PORT),
  secure: process.env.MAIL_ENCRYPTION === 'ssl',
  auth: {
    user: process.env.MAIL_USERNAME,
    pass: process.env.MAIL_PASSWORD,
  },
});

const sendWelcomeEmail = async (toEmail, userName) => {
  const mailOptions = {
    from: `"${process.env.MAIL_FROM_NAME}" <${process.env.MAIL_FROM_ADDRESS}>`,
    to: toEmail,
    subject: 'Selamat Datang di Studio Hasanarofid!',
    html: `
      <div style="font-family: sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;">
        <h2 style="color: #4F46E5;">Halo ${userName}!</h2>
        <p>Terima kasih telah bergabung dengan <strong>studio.hasanarofid.site</strong>.</p>
        <p>Akun Anda telah berhasil dibuat. Sekarang Anda dapat mulai menjelajahi dan membeli template website premium kami.</p>
        <div style="margin: 30px 0;">
          <a href="http://localhost:3000" style="background: #000; color: #fff; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;">Mulai Cari Template</a>
        </div>
        <p style="color: #666; font-size: 0.9rem;">Jika Anda tidak merasa mendaftar di situs kami, abaikan email ini.</p>
        <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
        <p style="font-size: 0.8rem; color: #999;">&copy; 2026 Studio Hasanarofid. Semua hak dilindungi.</p>
      </div>
    `,
  };

  try {
    await transporter.sendMail(mailOptions);
    console.log(`Welcome email sent to ${toEmail}`);
  } catch (error) {
    console.error('Error sending email:', error);
  }
};

module.exports = { sendWelcomeEmail };
