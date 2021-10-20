function search_bar() {
    let input = document.getElementById('searchbar').value
    console.log(input)
    input = input.toLowerCase();
    let x = document.getElementsByClassName('animals');

    for (i = 0; i < x.length; i++) {
        console.log(x[i].innerHTML)
        if (!x[i].innerHTML.toLowerCase().includes(input)) {
            x[i].style.display = "none";
        } else {
            x[i].style.display = "list-item";
        }
    }
}