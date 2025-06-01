<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Contact - EduMat</title>
    <link rel="stylesheet" href="../css/contact.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
</head>

<body>

    <header class="header">
        <div class="container">
            <h1>📞 Contactez-nous</h1>
            <nav>
                <a href="home0.php" class="btn">🏠 Accueil</a>
            </nav>
        </div>
    </header>

    <main class="contact-container">
        <section class="contact-intro">
            <h2>Besoin d’aide ? Nous sommes là pour vous.</h2>
            <p>Notre équipe vous répond dans les plus brefs délais. N’hésitez pas à poser votre question !</p>
        </section>

        <form id="contact-form" class="contact-form" method="POST" action="../php/contact_handler.php">
            <div class="form-group">
                <label for="nom">Nom complet</label>
                <input type="text" id="nom" name="nom" placeholder="Ex: Sarah Benali" required />
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Ex: sarah@email.com" required />
            </div>

            <div class="form-group">
                <label for="telephone">Numéro de téléphone</label>
                <input type="tel" id="telephone" name="telephone" placeholder="Ex: 0555 55 55 55" />
            </div>

            <div class="form-group">
                <label for="message">Message</label>
                <textarea id="message" name="message" placeholder="Écrivez ici votre message..." required></textarea>
            </div>

            <button type="submit" class="btn green">📨 Envoyer le message</button>
        </form>

        <div class="confirmation hidden" id="confirmationMessage">
            ✅ Merci pour votre message ! Nous vous contacterons bientôt.
        </div>

        <script>
            // Affiche la confirmation si redirection avec ?sent=1
            if (window.location.search.includes("sent=1")) {
                document.getElementById("confirmationMessage").classList.remove("hidden");
            }
        </script>


        <div class="confirmation hidden" id="confirmationMessage">

            ✅ Merci pour votre message ! Nous vous contacterons bientôt.
        </div>
        <script>
            // Check if URL has ?sent=1 to show the confirmation message
            if (window.location.search.includes("sent=1")) {
                const messagee = document.getElementById("confirmationMessage");
                messagee.classList.remove("hidden");

                // Hide the message after 5 seconds
                setTimeout(() => {
                    messagee.classList.add("hidden");
                }, 5000);
            }
        </script>
        <style>
            .hidden {
                display: none;
            }
        </style>

    </main>

    <footer class="footer">
        <p>&copy; 2025 EduMat. Tous droits réservés.</p>
    </footer>

    <script src="../js/contact.js"></script>
</body>

</html>