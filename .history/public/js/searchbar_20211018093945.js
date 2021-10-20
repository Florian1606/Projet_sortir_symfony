function search_sorties() {
    let searchbarValue = document.getElementById('searchbar').value.toLowerCase();
    console.log(searchbarValue)
    let sorties_nom = document.getElementsByClassName('sortie_nom');
    console.log("parent :" + sorties_nom.parentNode);
    let sorties = document.getElementsByClassName('sortie');

    for (i = 0; i < sorties_nom.length; i++) {
        console.log("parent :" + sorties_nom[i].parentNode.innerHTML);
        if (!sorties_nom[i].innerHTML.toLowerCase().includes(searchbarValue)) {
            // On cache la sortie
            sorties_nom[i].parentNode.style.display = "none";
        } else {
            // On laisse afficher la sortie
            sorties_nom[i].parentNode.style.display = "";
            //sorties_nom[i].style.display = "list-item";
        }
    }
}