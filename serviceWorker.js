
/** Cache the assets */

const staticDevCoffee = "dev-coffee-site-v1"
const assets = [
    "/",
    "/index.php",
    "/css/style.css",
    "/css/form.css",
    "/css/messages.css",
    "/js/scripts.js",
    "/js/form.js",
    "/js/jquery-3.4.1.min.js",
    "/js/jquery-ui.js",
    "/js/jquery-ui.css",
    "/inc/messages.inc.php",
    "/login/guest.php",
    "/login/index.php",
    "/login/login-process.php",
    "/login/logout.php",
    "/register/check_new_user.php",
    "/register/index.php",
    "/register/register-process.php",
    "/reset/email-process.php",
    "/reset/email-success.php",
    "/reset/emailpass.php",
    "/reset/index.php",
    "/reset/reset-success.php",
    "/reset/reset-process.php",
    "/dbcon.php",
    "/error.php",
    "/functions.php",
    "/Guest.php",
    "/insert_text.php",
    "/Respond.php",
    "/respondController.php",
    "/UserClass.php",

    "/images/ai_bg.jpg",
    "/images/ai_bg2.jpg",
    "/images/check_icon.png",
    "/images/loading.gif",
    "/images/logo.png",

]

self.addEventListener("install", installEvent => {
    installEvent.waitUntil(
        caches.open(staticDevCoffee).then(cache => {
            cache.addAll(assets)
        })
    )
})


/** Fetch the assets */

self.addEventListener("fetch", fetchEvent => {
    fetchEvent.respondWith(
        caches.match(fetchEvent.request).then(res => {
            return res || fetch(fetchEvent.request)
        })
    )
})