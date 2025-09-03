$(function() {
    console.log('jquery ready!');
    $('#form_category').on('change', function() {
        var id    = parseInt($(this).val())+1;
        const path  = $(this).data('path');
        $.post(path, {id: id, ajax: 1}, function(response, textstatus, xhr) {
            if(xhr.status==200) {
                $('#form_subcategory').empty();
                $('<option>').val('').text('').appendTo('#form_subcategory');
                $.each(response, function(subid, text) {
                    $('<option>').val(subid-1).text(text).appendTo('#form_subcategory');
                });
                $('#form_subcategory').removeClass('bg-white dark:bg-white/5 dark:*:bg-gray-800 dark:outline-white/10 outline-gray-300');
                $('#form_subcategory').addClass('ring-3 ring-green-600/20 bg-green-50 outline-green-600/20 animate-pulse');
                setTimeout(function() {
                    $('#form_subcategory').addClass('bg-white dark:bg-white/5 dark:*:bg-gray-800 dark:outline-white/10 outline-gray-300');
                    $('#form_subcategory').removeClass('ring-3 ring-green-600/20 bg-green-50 outline-green-600/20 animate-pulse');
                }, 1500);
            }
        });
    });
    $('#form_scale').on('change', function() {
        var id    = parseInt($(this).val())+1;
        const path  = $(this).data('path');
        $.post(path, {id: id, ajax: 1}, function(response, textstatus, xhr) {
            if(xhr.status==200) {
                $('#form_track').empty();
                $('<option>').val('').text('').appendTo('#form_track');
                $.each(response, function(subid, text) {
                    $('<option>').val(subid-1).text(text).appendTo('#form_track');
                });
                $('#form_track').removeClass('bg-white dark:bg-white/5 dark:*:bg-gray-800 dark:outline-white/10 outline-gray-300');
                $('#form_track').addClass('ring-3 ring-green-600/20 bg-green-50 outline-green-600/20 animate-pulse');
                setTimeout(function() {
                    $('#form_track').addClass('bg-white dark:bg-white/5 dark:*:bg-gray-800 dark:outline-white/10 outline-gray-300');
                    $('#form_track').removeClass('ring-3 ring-green-600/20 bg-green-50 outline-green-600/20 animate-pulse');
                }, 1500);
            }
        });
    });
    $('#form_epoch').on('change', function() {
        var id    = parseInt($(this).val())+1;
        const path  = $(this).data('path');
        $.post(path, {id: id, ajax: 1}, function(response, textstatus, xhr) {
            if(xhr.status==200) {
                $('#form_subepoch').empty();
                $('<option>').val('').text('').appendTo('#form_subepoch');
                $.each(response, function(subid, text) {
                    $('<option>').val(subid-1).text(text).appendTo('#form_subepoch');
                });
                $('#form_subepoch').removeClass('bg-white dark:bg-white/5 dark:*:bg-gray-800 dark:outline-white/10 outline-gray-300');
                $('#form_subepoch').addClass('ring-3 ring-green-600/20 bg-green-50 outline-green-600/20 animate-pulse');
                setTimeout(function() {
                    $('#form_subepoch').addClass('bg-white dark:bg-white/5 dark:*:bg-gray-800 dark:outline-white/10 outline-gray-300');
                    $('#form_subepoch').removeClass('ring-3 ring-green-600/20 bg-green-50 outline-green-600/20 animate-pulse');
                }, 1500);
            }
        });
    });

    const dropZone = document.getElementById("drop-zone");
    $('#drop-zone').on('drop', function(event) {
        dropHandler(event);
    });
    $('#drop-zone').on('dragover', function(event) {
        dropZone.classList.add('bg-gray-100');
        event.preventDefault();
    });
    $('#drop-zone').on('dragleave', function(event) {
        dropZone.classList.remove('bg-gray-100');
        event.preventDefault();
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
    function dropHandler(event) {
        // Prevent default behavior (Prevent file from being opened)
        event.preventDefault();
        let result = "";
        dropZone.classList.remove('bg-gray-100');
        // Use DataTransferItemList interface to access the file(s)
        console.log(event.originalEvent.dataTransfer.files);
        [...event.originalEvent.dataTransfer.items].forEach((item, i) => {
            // If dropped items aren't files, reject them
            if (item.kind === "file") {
                const file = item.getAsFile();
                if (validFileType(file)) {
                    document.getElementById(('filetype-error')).classList.add('hidden');
                    document.getElementById(('drop-zone')).classList.remove('border-red-600','dark:border-red-400');
                    const src = URL.createObjectURL(file);
                    const image = document.createElement("img");
                    image.classList.add('mx-auto','size-12','text-gray-300','dark:text-gray-500');
                    image.src = src;
                    document.getElementById(('preview-icon')).remove();
                    document.getElementById('form_image').files = event.originalEvent.dataTransfer.files;
                    document.getElementById('icon-holder').append(image);
                    console.log(document.getElementById('form_image').value);
                } else {
                    document.getElementById(('filetype-error')).classList.remove('hidden');
                    document.getElementById(('drop-zone')).classList.add('border-red-600','dark:border-red-400');
                    console.log('wrong.file.type');
                }
            }
        });
    }
});
