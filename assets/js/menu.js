console.log("hello menu.js");

const navbarMenu = document.querySelector(".navbar-menu");
const burger = document.querySelector(".burger");
const closeBtn = document.querySelector(".close");

const showHideNavbar = () => {
  navbarMenu.classList.toggle("hideNav");
  navbarMenu.classList.toggle("showNav");
  closeBtn.classList.toggle("dnone");
  burger.classList.toggle("dnone");
};
burger.addEventListener("click", showHideNavbar);
closeBtn.addEventListener("click", showHideNavbar);

