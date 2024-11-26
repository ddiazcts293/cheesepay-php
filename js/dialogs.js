function initDialogs() {
    let modals = document.getElementsByTagName('dialog');

    for (let i = 0; i < modals.length; i++) {
        let dialog = modals[i];
        let closeButtons = dialog.getElementsByClassName('dialog-close-btn');
        
        for (let j = 0; j < closeButtons.length; j++) {
            closeButtons[j].onclick = () => {
                dialog.close();
            };
        }
    }
}

function showDialog(modalElementId) {
    let modal = document.getElementById(modalElementId);
    modal.showModal();
}


function hideDialog(modalElementId) {
    let modal = document.getElementById(modalElementId);
    modal.close();
}

window.addEventListener('load', () => initDialogs());
