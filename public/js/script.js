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