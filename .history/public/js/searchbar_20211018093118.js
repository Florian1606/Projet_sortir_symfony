function search_sorties() {
    let searchbarValue = document.getElementById('searchbar').value;
    console.log(searchbarValue)
    searchbarValue = input.toLowerCase();
    let sorties_nom = document.getElementsByClassName('sortie_nom');


    for (i = 0; i < x.length; i++) {
        console.log(x[i].innerHTML)
        if (!x[i].innerHTML.toLowerCase().includes(input)) {
            x[i].style.display = "none";
        } else {
            x[i].style.display = "list-item";
        }
    }
}