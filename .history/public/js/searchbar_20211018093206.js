function search_sorties() {
    let searchbarValue = document.getElementById('searchbar').value;
    console.log(searchbarValue)
    searchbarValue = searchbarValue.toLowerCase();
    let sorties_nom = document.getElementsByClassName('sortie_nom');
    console.log(sorties_nom);
    /*
    for (i = 0; i < sorties_nom.length; i++) {
        if (!sorties_nom[i].innerHTML.toLowerCase().includes(input)) {
            sorties_nom[i].style.display = "none";
        } else {
            sorties_nom[i].style.display = "list-item";
        }
    }*/
}