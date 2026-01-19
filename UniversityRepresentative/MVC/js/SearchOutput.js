function showLoginMessage() {
  // Get the warning message element
  var msg = document.getElementById("login-warning-msg");

  // Check if element exists to prevent errors
  if (msg) {
    // Make the message visible
    msg.style.display = "block";

    // Smooth scroll to the message so the user notices it
    msg.scrollIntoView({ behavior: "smooth", block: "center" });
  }
}
