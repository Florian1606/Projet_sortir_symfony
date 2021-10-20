function search_sorties() {
    let searchbarValue = document.getElementById('searchbar').value.toLowerCase();
    console.log(searchbarValue)
    let sorties_nom = document.getElementsByClassName('sortie_nom');
    console.log(sorties_nom);

    for (i = 0; i < sorties_nom.length; i++) {
        if (!sorties_nom[i].innerHTML.toLowerCase().includes(searchbarValue)) {
            // On cache la sortie
            sorties_nom[i].style.display = "none";
        } else {
            // On laisse afficher la sortie
            sorties_nom[i].style.display = "list-item";
        }
    }
}