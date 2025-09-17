$(function() {
    $('#submit-form').on('click', function() {
        $('#modelform').submit();
        return false;
    });
    if($('.mbs_model').length>0) {
        var id = parseInt($('#form_category').find(':selected').data('id'));
        $('#form_subcategory .subcategory-option').hide();
        $('#form_subcategory .category-'+id).show();
        var id = parseInt($('#form_epoch').find(':selected').data('id'));
        $('#form_subepoch .subepoch-option').hide();
        $('#form_subepoch .epoch-'+id).show();
        var id = parseInt($('#form_scale').find(':selected').data('id'));
        $('#form_track .track-option').hide();
        $('#form_track .scale-'+id).show();
    }
    if($('#form_state').length>=0) {
        var id = parseInt($('#form_country').find(':selected').data('id'));
        $('#form_state .state-option').hide();
        $('#form_state .country-'+id).show();
    }
    $('#form_country').on('change', function() {
        if($('#form_state').length==0) {
            return;
        }
        var id    = parseInt($(this).find(':selected').data('id'));
        $('#form_state .state-option').hide();
        $('#form_state .country-'+id).show();
        $('#form_state option:first-child').attr('selected', true);
        $('#form_state').removeClass('bg-white dark:bg-white/5 dark:*:bg-gray-800 dark:outline-white/10 outline-gray-300');
        $('#form_state').addClass('ring-3 ring-green-600/20 bg-green-50 outline-green-600/20 animate-pulse');
        setTimeout(function() {
            $('#form_state').addClass('bg-white dark:bg-white/5 dark:*:bg-gray-800 dark:outline-white/10 outline-gray-300');
            $('#form_state').removeClass('ring-3 ring-green-600/20 bg-green-50 outline-green-600/20 animate-pulse');
        }, 1500);
    });
    $('#form_category').on('change', function() {
        var id    = parseInt($(this).find(':selected').data('id'));
        $('#form_subcategory .subcategory-option').hide();
        $('#form_subcategory .category-'+id).show();
        $('#form_subcategory option:first-child').attr('selected', true);
        $('#form_subcategory').removeClass('bg-white dark:bg-white/5 dark:*:bg-gray-800 dark:outline-white/10 outline-gray-300');
        $('#form_subcategory').addClass('ring-3 ring-green-600/20 bg-green-50 outline-green-600/20 animate-pulse');
        setTimeout(function() {
            $('#form_subcategory').addClass('bg-white dark:bg-white/5 dark:*:bg-gray-800 dark:outline-white/10 outline-gray-300');
            $('#form_subcategory').removeClass('ring-3 ring-green-600/20 bg-green-50 outline-green-600/20 animate-pulse');
        }, 1500);
    });
    $('#form_scale').on('change', function() {
        var id    = parseInt($(this).find(':selected').data('id'));
        $('#form_track .track-option').hide();
        $('#form_track .scale-'+id).show();
        $('#form_track option:first-child').attr('selected', true);
        $('#form_track').removeClass('bg-white dark:bg-white/5 dark:*:bg-gray-800 dark:outline-white/10 outline-gray-300');
        $('#form_track').addClass('ring-3 ring-green-600/20 bg-green-50 outline-green-600/20 animate-pulse');
        setTimeout(function() {
            $('#form_track').addClass('bg-white dark:bg-white/5 dark:*:bg-gray-800 dark:outline-white/10 outline-gray-300');
            $('#form_track').removeClass('ring-3 ring-green-600/20 bg-green-50 outline-green-600/20 animate-pulse');
        }, 1500);
    });
    $('#form_epoch').on('change', function() {
        var id    = parseInt($(this).find(':selected').data('id'));
        $('#form_subepoch .subepoch-option').hide();
        $('#form_subepoch .epoch-'+id).show();
        $('#form_subepoch option:first-child').attr('selected', true);
        $('#form_subepch').removeClass('bg-white dark:bg-white/5 dark:*:bg-gray-800 dark:outline-white/10 outline-gray-300');
        $('#form_subepoch').addClass('ring-3 ring-green-600/20 bg-green-50 outline-green-600/20 animate-pulse');
        setTimeout(function() {
            $('#form_subepoch').addClass('bg-white dark:bg-white/5 dark:*:bg-gray-800 dark:outline-white/10 outline-gray-300');
            $('#form_subepoch').removeClass('ring-3 ring-green-600/20 bg-green-50 outline-green-600/20 animate-pulse');
        }, 1500);
    });

    // Logo, Color and Country-Update
    $('#language').on('change', function() {
        console.log('changed');
        const href = $('#language').find(':selected').data('href');
        window.location.href = href;
    });
    $('#form_manufacturer').on('change', function() {
        const image = $('#form_manufacturer').find(':selected').data('image');
        if(typeof image != 'undefined' && image!="") {
            $('#image-manufacturer').attr('src', "/data/logo/manufacturer/"+image).addClass('rounded-md outline-1 dark:outline-white/10 outline-gray-300');
        } else {
            $('#image-manufacturer').attr('src', "/images/blank.svg").attr('class', '');
        }
    });
    $('#form_dealer').on('change', function() {
        const image = $('#form_dealer').find(':selected').data('image');
        if(typeof image != 'undefined' && image!="") {
            $('#image-dealer').attr('src', "/data/logo/dealer/"+image).addClass('rounded-md outline-1 dark:outline-white/10 outline-gray-300');
        } else {
            $('#image-dealer').attr('src', "/images/blank.svg").attr('class', '');
        }
    });
    $('#form_country').on('change', function() {
        const image = $('#form_country').find(':selected').data('image');
        if(typeof image != 'undefined' && image!="") {
            $('#image-flag').attr('src', "/data/flag/"+image).addClass('rounded-md outline-1 dark:outline-white/10 outline-gray-300');
        } else {
            $('#image-flag').attr('src', "/images/blank.svg").attr('class', '');
        }
    });
    $('#form_company').on('change', function() {
        const image = $('#form_company').find(':selected').data('image');
        const country = $('#form_company').find(':selected').data('country');
        const color1 = $('#form_company').find(':selected').data('color1');
        const color2 = $('#form_company').find(':selected').data('color2');
        const color3 = $('#form_company').find(':selected').data('color3');
        if(typeof country != 'undefined' && country!="") {
            $('#form_country option[data-iso="'+country+'"]').prop('selected', true);
            $('#image-flag').attr('src', "/data/flag/"+country+".svg").addClass('rounded-md outline-1 dark:outline-white/10 outline-gray-300');
        } else {
            $('#image-flag').attr('src', "/images/blank.svg").attr('class', '');
        }
        if(typeof image != 'undefined' && image!="") {
            $('#image-company').attr('src', "/data/logo/company/"+image).addClass('rounded-md outline-1 dark:outline-white/10 outline-gray-300');
        } else {
            $('#image-company').attr('src', "/images/blank.svg").attr('class', '');
        }
        if(typeof color1 != 'undefined' && color1!="") {
            $('#form_color1').val(color1);
        } else {
            $('#form_color1').val("rgba(255,255,255,0)");
        }
        if(typeof color2 != 'undefined' && color2!="") {
            $('#form_color2').val(color2);
        } else {
            $('#form_color2').val("rgba(255,255,255,0)");
        }
        if(typeof color3 != 'undefined' && color3!="") {
            $('#form_color3').val(color3);
        } else {
            $('#form_color3').val("rgba(255,255,255,0)");
        }
    });


    // EAN caluclation
    $('#form_manufacturer').on('change', function() {
        const gtinbase = $('#form_manufacturer').find(':selected').data('gtin-base');
        const gtinmode = $('#form_manufacturer').find(':selected').data('gtin-mode');
        const model = $('#form_model').val();
        if(gtinmode!="" && model!="") {
            gtin = gtinbase+model+(checkDigitEAN13(gtinbase+model));
            if($('#form_ean').val()=="") {
                $('#form_ean').val(gtin);
            } else {
                console.log(gtin);
            }
        }
    })

    function checkDigitEAN13(barcode) {
        const sum = barcode.split('')
            .map((n, i) => n * (i % 2 ? 3 : 1)) // alternate between multiplying with 3 and 1
            .reduce((sum, n) => sum + n, 0) // sum all values
        const roundedUp = Math.ceil(sum / 10) * 10; // round sum to nearest 10
        const checkDigit = roundedUp - sum; // subtract round to sum = check digit
        return checkDigit;
    }

    $('#load').on('keyup', function() {
        const search = $('#load').val();
        const path = $('#load').data('path');
        if(search.length<3) {
            return false;
        }
        //$('#el-options').html('<el-option value="Courtney Henry" class="block px-3 py-2 text-gray-900 select-none aria-selected:bg-indigo-600 aria-selected:text-white dark:text-white dark:aria-selected:bg-indigo-500" id="option-6" role="option" aria-selected="false" tabindex="-1"> <div class="flex"> <span class="truncate">Courtney Henry</span> <span class="ml-2 truncate text-gray-500 in-aria-selected:text-white dark:text-gray-400 dark:in-aria-selected:text-white">@courtneyhenry</span> </div> </el-option>');
        $.get(path, {search: search, ajax: 1}, function (response, textstatus, xhr) {
            if (xhr.status == 200) {
                $('#el-options').empty();
                $.each(response, function(id, data) {
                    $('<a>').attr('data-id', id).attr('value', data.name).html('<div class="flex justify-between"><span class="truncate">'+data.name+'</span> <span class="ml-2 truncate text-gray-300 hover:text-white dark:text-gray-200 dark:hover:text-white">'+data.model+'</span></div>').attr('class', 'block px-3 py-2 text-gray-900 select-none hover:bg-green-800 hover:text-white dark:text-white dark:hover:bg-green-700').appendTo('#el-options');
                })
                $('#el-options a').on('click', function() {
                    $('#el-model').val($(this).data('id'));
                    $('#el-modelname').val($(this).attr('value'));
                    $('#load').val($(this).attr('value'));
                    $('#el-options').hide();
                    $('#add-load').prop('disabled', false);
                })
                $('#el-options').show();
            }
        });
    })
    $('#load').on('blur', function () {
        setTimeout(function() {
            $('#el-options').hide();
        }, 500);

    })

    $('#mobile-nav').on('change', function () {
        location.replace($('#mobile-nav').find(':selected').data('path'));
    });
    $('.delete-function').on('click', function () {
        $('#dialog-title').html($(this).data('headline'));
        $('#dialog-action').attr('href', $(this).data('path'));
    });

    $('#add-load').on('click', function(event) {
        const model = $('#model').val();
        const load = $('#el-model').val();
        const loadname = $('#el-modelname').val();
        const path = $('#add-path').val();
        var template = $('#row-template');
        var row = $('<tr>').html(template.html());
        row.find('#template-row-model').html(loadname);
        if($('#load').val()!="") {
            $.post(path, {model: model, load: load, ajax: 1}, function (response, textstatus, xhr) {
                if (xhr.status == 200) {
                    $('#load').focus();
                    $('#load').val('');
                    $('#add-load').prop('disabled', true);
                    $('#el-model').val('');
                    $('#el-modelname').val('');
                    row.appendTo($('#loads tbody'));
                }
            });
        }
        event.preventDefault();
    });

    $('#add-function').on('click', function (event) {
        const key = $('#add-key').val();
        const keytext = $('#add-key').find(':selected').text();
        const decoderfunction = $('#add-decoderfunction').val();
        const sound = $('#add-sound').is(':checked');
        const light = $('#add-light').is(':checked');
        const path = $('#add-path').val();
        const model = $('#add-model').val();
        var template = $('#row-template');
        var row = $('<tr>').html(template.html());
        if(sound!=1) {
            row.find('#template-row-sound button').remove();
        }
        if(light!=1) {
            row.find('#template-row-light button').remove();
        }
        row.find('#template-row-key').html(keytext);
        row.find('#template-row-decoderfunction').html(decoderfunction);
        if(key!="" && decoderfunction!="")
        {
            $.post(path, {model: model, sound: sound, light: light, key: key, decoderfunction: decoderfunction, ajax: 1}, function (response, textstatus, xhr) {
                if (xhr.status == 200) {
                    $('#add-key').focus();
                    $('#add-decoderfunction').val('');
                    $('#add-sound').prop('checked', false);
                    $('#add-light').prop('checked', false);
                    row.appendTo($('#functions tbody'));
                }
            });
        }
        event.preventDefault();
    })

    $('.add-value').on('click', function(event) {
        $('#drawer-title').html($(this).data('title'))
        $('#drawer-label').html($(this).data('label'))
        $('#drawer-entity').val($(this).data('entity'));
        const parent = $(this).data('parent');
        if(typeof parent != 'undefined' && parent!="") {
            $('#drawer-parent').val(parent);
            $('#parent').html($("label[for='form_"+parent+"']").text());
            $('#parent-wrapper').show();
        } else {
            $('#drawer-parent').val('');
            $('#parent-wrapper').hide();
        }
    });
    $('#add-value').on('click', function(event) {
       const entity = $('#drawer-entity').val();
       const name = $('#drawer-name').val();
       const path = $('#drawer-path').val();
       const parententity = $('#drawer-parent').val();
       const parent = $('#form_'+parententity).val();
       if(name!="") {
           $.post(path, {name: name, entity: entity, parent: parent, ajax: 1}, function (response, textstatus, xhr) {
               if (xhr.status == 200) {
                   $('#form_'+entity).empty();
                   $('<option>').val('').text('').appendTo('#form_'+entity);
                   $.each(response, function(subid, text) {
                       $('<option>').val(subid-1).text(text).prop('selected', text==name ? true : false).appendTo('#form_'+entity);
                   });
                   $('#drawer-name').val('');
                   $('#close-drawer').click();
                   $('#form_'+entity).removeClass('bg-white dark:bg-white/5 dark:*:bg-gray-800 dark:outline-white/10 outline-gray-300');
                   $('#form_'+entity).addClass('ring-3 ring-green-600/20 bg-green-50 outline-green-600/20 animate-pulse');
                   setTimeout(function() {
                       $('#form_'+entity).addClass('bg-white dark:bg-white/5 dark:*:bg-gray-800 dark:outline-white/10 outline-gray-300');
                       $('#form_'+entity).removeClass('ring-3 ring-green-600/20 bg-green-50 outline-green-600/20 animate-pulse');
                   }, 1500);

               }
           });
       }
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
        "image/svg+xml",
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
