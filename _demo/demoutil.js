// document.getElementById('error').innerHTML = '';

function openNav() {
    document.getElementById("mySidebar").style.width = "250px";
    document.getElementById("main").style.marginLeft = "250px";
    // Hide button, show sidebar.
    // document.getElementById('openbtn').style.display = 'none';
    // document.getElementById('mySidebar').style.display = '';
}

function closeNav() {
    document.getElementById("mySidebar").style.width = "0";
    document.getElementById("main").style.marginLeft = "0";
    //Show button, hide sidebar.
    // document.getElementById('openbtn').style.display = '';
    // document.getElementById('mySidebar').style.display = 'none';
}

// Fake logout.
function logout() {
    location.href = 'index.php';
}

//Negligible. Not used.
function dummyLogin(strACC, strPWD) {
    var strACC = String(strACC);
    const strAdmin = 'admin';
    const strSeller = 'seller';
    if (strPWD == '') {
        alert('Password is empty!');
        return null;
    }
    if (strACC.indexOf(strAdmin) !== -1) {
        //write username to file.
        location.href = 'index_admin.html';
    } else if (strACC.indexOf(strSeller) !== -1) {
        //write username to file.
        location.href = 'index_seller.html';
    } else {
        alert('Invalid account!');
        return null;
    }
}