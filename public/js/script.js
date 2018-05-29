function changeDate() {
    document.getElementById('formDate').submit();
}

function clickAcheter() {
    document.getElementById("acheter").value = "true";
    submitPhoto();
}


function submitPhoto() {
    document.getElementById("formBuyPhoto").submit();
}
function clickShare() {
    TheImg=document.getElementById('imageToShare');
    u=TheImg.src;
    t=TheImg.getAttribute('alt');
    window.open('http://www.facebook.com/sharer.php?u='+encodeURIComponent(u)+'&t='+encodeURIComponent(t),'sharer','toolbar=0,status=0,width=626,height=436');return false;
}
function selectFormat(prix) {
    document.getElementById('prixPhoto').innerHTML = "Prix de la photo :" +  prix  + "€"
}

function getFileName(fileId)  {
    var x = document.getElementById(fileId)
    x.style.visibility = 'collapse'
    var fichiers = x.files
    if (fichiers.length == 1) {
        document.getElementById('fileNamePhoto').innerHTML = fichiers[0].name
    } else {
        document.getElementById('fileNamePhoto').innerHTML = fichiers.length + " photos sélectionnées"
    }
}

var slideIndex = 0;
showSlides();

function showSlides() {
    var i;
    var slides = document.getElementsByClassName("carousel-background");
    for (i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";
    }
    slideIndex++;
    if (slideIndex > slides.length) {slideIndex = 1}
    slides[slideIndex-1].style.display = "block";
    setTimeout(showSlides, 5000); // Change image every 2 seconds
}

function changeCategory(category) {
    if (category == 'particulier') {
        document.getElementById("formatProfessionnel").hidden = true;
        document.getElementById("formatParticulier").hidden = false;
        document.getElementById("selectParticulier").name = 'format';
        document.getElementById("selectProfessionnel").name = '';
    } else {
        document.getElementById("formatProfessionnel").hidden = false;
        document.getElementById("formatParticulier").hidden = true;
        document.getElementById("selectParticulier").name = ''
        document.getElementById("selectProfessionnel").name = 'format';
    }
}