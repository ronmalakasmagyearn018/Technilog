(function () {

  function goToAuth(e) {
    if (e) e.preventDefault();
    const isLoggedIn = localStorage.getItem('tl_logged_in') === 'true';
    const target = isLoggedIn
      ? '../auth/account.html'
      : '../auth/login.html';
    window.location.href = target;
  }

  // userIcon now opens avatar modal — do NOT attach goToAuth to it
  const menuAccountLink = document.getElementById('menuAccountLink');
  const contactLoginBtn = document.getElementById('contactLoginBtn');

  if (menuAccountLink) menuAccountLink.addEventListener('click', goToAuth);
  if (contactLoginBtn) contactLoginBtn.addEventListener('click', goToAuth);

})();