function search_sorties() {
    let searchbarValue = document.getElementById('searchbar').value;
    console.log(searchbarValue)
    searchbarValue = input.toLowerCase();
    let sorties_nom = document.getElementsByClassName('sortie_nom');


    for (i = 0; i < sorties_nom.length; i++) {
        if (!sorties_nom[i].innerHTML.toLowerCase().includes(input)) {
            x[i].style.display = "none";
        } else {
            x[i].style.display = "list-item";
        }
    }
}