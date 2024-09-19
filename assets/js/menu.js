console.log("hello menu.js");

// Format mobile 
// affichage menu

const navbarMenu = document.querySelector(".navbar-menu");
const burger = document.querySelector(".burger");
const closeBtn = document.querySelector(".close");
const logoMobile = document.querySelector(".logoMobile");


const showHideNavbar = () => {
  navbarMenu.classList.toggle("hideNav");
  navbarMenu.classList.toggle("showNav");
  closeBtn.classList.toggle("dnone");
  burger.classList.toggle("dnone");
  logoMobile.classList.toggle("dnone");

};
burger.addEventListener("click", showHideNavbar);
closeBtn.addEventListener("click", showHideNavbar);


// Tous affichages 
// Menu déroulant pour compte user

function subMenu(menuButton, menuBloc) {
  const button = document.querySelector(menuButton);
  const bloc = document.querySelector(menuBloc);
  const menuFrame = button.nextElementSibling;
  menuFrame.style.transition = "all 0s";
  menuFrame.style.maxHeight = "0";
  menuFrame.style.overflow = "hidden";
  menuFrame.classList.toggle("navbar-menu-items-li");

  bloc.addEventListener("mouseenter", () => {
      menuFrame.classList.toggle("navbar-menu-items-li");
      
      menuFrame.style.maxHeight = "40rem";
      menuFrame.style.overflow = "visible";
      button.classList.toggle("category-active");
  });
  bloc.addEventListener("mouseleave", () => {
     
      menuFrame.classList.toggle("menu-category-items");
      menuFrame.style.maxHeight = "0";
      menuFrame.style.overflow = "hidden";
      button.classList.toggle("category-active");
  });
}

// j'appelle ma fonction

subMenu("#userMenu", "#userMenuBloc");