import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');

const dropZone = document.getElementById("drop-zone");
dropZone.addEventListener("drop", dropHandler);
dropZone.addEventListener("dragover", (e) => {
    dropZone.classList.add('bg-gray-100');
    e.preventDefault();
});
dropZone.addEventListener("dragleave", (e) => {
    dropZone.classList.remove('bg-gray-100');
    e.preventDefault();
});
window.addEventListener("dragover", (e) => {
    e.preventDefault();
});
window.addEventListener("drop", (e) => {
    e.preventDefault();
});
const selectElement = document.querySelector("#form_image");

const fileTypes = [
    "image/jpeg",
    "image/jpg",
    "image/png",
    "image/webp",
];

function validFileType(file) {
    return fileTypes.includes(file.type);
}
function dropHandler(ev) {
    // Prevent default behavior (Prevent file from being opened)
    ev.preventDefault();
    let result = "";
    dropZone.classList.remove('bg-gray-100');
    // Use DataTransferItemList interface to access the file(s)
    [...ev.dataTransfer.items].forEach((item, i) => {
        // If dropped items aren't files, reject them
        if (item.kind === "file") {
            const file = item.getAsFile();
            if (validFileType(file)) {
                const src = URL.createObjectURL(file);
                const image = document.createElement("img");
                image.classList.add('mx-auto','size-12','text-gray-300','dark:text-gray-500');
                image.src = src;
                document.getElementById(('preview-icon')).remove();
                document.getElementById('form_image').files = ev.dataTransfer.files;
                document.getElementById('icon-holder').append(image);
                console.log(document.getElementById('form_image').value);
            } else {
                console.log('wrong.file.type');
            }
        }
    });
}
