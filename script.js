let usernameRef = document.getElementById('username');
let passwordRef = document.getElementById('password');
let eyeRef = document.getElementById('togglePassword');
let passtype = document.getElementById('password');
let eyeL = document.querySelector(".eyeball-l");
let eyeR = document.querySelector(".eyeball-r");
let handL = document.querySelector(".hand-l");
let handR = document.querySelector(".hand-r");



function passUncover(){
  eyeL.style.cssText = `
  left:0.6em;
  top: 0.6em;
  margin-top: 0.5em;
  `;

  eyeR.style.cssText = `
  left:0.6em;
  top: 0.6em;
  margin-top: 0.5em;
  `;

  handL.style.cssText = `
        height: 2.81em;
        top:8.4em;
        left:7.5em;
        transform: rotate(0deg);
    `;
  handR.style.cssText = `
        height: 2.81em;
        top: 8.4em;
        right: 7.5em;
        transform: rotate(0deg)
    `;

}

function uncover(){
    eyeL.style.cssText = `
  left:0.6em;
  top: 0.6em;
  `;
  eyeR.style.cssText = `
  right:0.6em;
  top:0.6em;
  `;

  handL.style.cssText = `
        height: 2.81em;
        top:8.4em;
        left:7.5em;
        transform: rotate(0deg);
    `;
  handR.style.cssText = `
        height: 2.81em;
        top: 8.4em;
        right: 7.5em;
        transform: rotate(0deg)
    `;
};


function cover(){
    handL.style.cssText = `
        height: 6.56em;
        top: 3.87em;
        left: 11.75em;
        transform: rotate(-155deg);    
    `;
  handR.style.cssText = `
    height: 6.56em;
    top: 3.87em;
    right: 11.75em;
    transform: rotate(155deg);
  `;
};

passwordRef.addEventListener("focus",() =>{
    cover();
});


usernameRef.addEventListener("focus",() =>{
  eyeL.style.cssText = `
  left:0.6em;
  top: 0.6em;
  margin-top: 0.5em;
  `;

  eyeR.style.cssText = `
  left:0.6em;
  top: 0.6em;
  margin-top: 0.5em;
  `;
});


//When clicked outside username and password input
document.addEventListener("click", (e) => {
    let clickedElem = e.target;
    if (clickedElem != usernameRef && clickedElem != eyeRef && clickedElem!=passwordRef) {
      password.setAttribute('type', 'password');
      uncover();
    }
  });


//   toggle the password attribute and change the eye icon also call functions to cover and uncover eyes
eyeRef.addEventListener('click', function(e){
    const type = password.getAttribute('type')== 'password' ? 'text' : 'password';
    password.setAttribute('type', type);
    if (password.getAttribute('type') === 'text'){
        cover();
    }else{
        passUncover();
    };
    this.classList.toggle('fa-eye-slash');
  });