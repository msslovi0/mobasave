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
        var prefix = $(this).find(':selected').data('prefix');
        $('.phone-prefix').html(prefix);

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
            $('#form_country option:first').prop('selected', true);
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

    $('.remove-filter').on('click', function(event) {
        $("."+$(this).data('database')+"_"+$(this).data('value')).attr('checked', false);
        $(this).parent().fadeOut();
        $('#filterform').submit();
        return event.preventDefault();
    })


    // EAN caluclation
    $('#form_manufacturer').on('change', function() {
        const gtinbase = $('#form_manufacturer').find(':selected').data('gtin-base');
        const gtinmode = $('#form_manufacturer').find(':selected').data('gtin-mode');
        const model = $('#form_model').val();
        if(gtinmode!="" && model!="") {
            gtin = gtinbase+model+(checkDigitEAN13(gtinbase+model));
            const ean = $('#form_gtin13').val();
            console.log(ean);
            if(typeof ean != 'undefined' || ean=="") {
                $('#form_gtin13').val(gtin);
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

    $('#form_containertype').on('click', function() {
        var type = $('#form_containertype').find(':selected').text();
        switch(type) {
            case "22G1":
            case "22U1":
            case "2MT5":
            case "22T6":
            case "2276":
            case "25G1":
            case "2DT5":
            case "22R1":
            case "2275":
            case "2210":
            case "2KG8":
            case "2NGS":
            case "2NG8":
            case "2PG8":
            case "2PG2":
            case "2EG9":
            case "2EG1":
            case "EMT6":
            case "COIL":
                var length = "70.07"
            break;
            case "45G1":
            case "45R1":
            case "4FG1":
            case "4EG1":
            case "42G1":
                var length = "140.1"
            break;
            case "C 715":
                var length = "81.6"
            break;
            case "L5G1":
            case "LEG1":
                var length = "157.7"
            break;
            case "MFRG":
            case "MPR1":
            case "MFR1":
            case "MFR3":
                var length = "162.9"
            break;
            case "MFGB":
            case "MEG1":
            case "MFG1":
                var length = "168.2"
            break;
            case "3MB0":
            case "3DB0":
                var length = "105.1"
            break;
        }
        if(length!="") {
            $('#form_length').val(length);
        }
    });
    if($('#form_containertype').length) {
        if($('#form_registration').val()!="") {
            validateChecksum($('#form_registration').val());
        }
        $('#form_registration').on('keyup', function() {
            validateChecksum($('#form_registration').val());
        })
    }

    function validateChecksum(value) {
        if(value.length==0) {
            $('#registration-message').html('');
            $('#registration-icon').html('');
        } else if(value.length<10 || value.length>11) {
            $('#registration-message').html($('#registration-message').data('error-length'));
            $('#registration-icon').html('<path d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM8.28 7.22a.75.75 0 0 0-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 1 0 1.06 1.06L10 11.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L11.06 10l1.72-1.72a.75.75 0 0 0-1.06-1.06L10 8.94 8.28 7.22Z" clip-rule="evenodd" fill-rule="evenodd"></path>');
            $('#registration-icon').addClass('text-red-600 dark:text-red-400');
            $('#registration-icon').removeClass('text-amber-500 dark:text-amber-400 text-green-600 dark:text-green-400');
            $('#registration-message').removeClass('text-amber-500 text-green-500');
            $('#registration-message').addClass('text-red-500');
        } else {
            // error <path d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM8.28 7.22a.75.75 0 0 0-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 1 0 1.06 1.06L10 11.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L11.06 10l1.72-1.72a.75.75 0 0 0-1.06-1.06L10 8.94 8.28 7.22Z" clip-rule="evenodd" fill-rule="evenodd"></path>
            // warning <path d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495ZM10 5a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 5Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" fill-rule="evenodd"></path>
            // success <path d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" fill-rule="evenodd"></path>
            const path = $('#form_registration').data('path');
            $.post(path, {value: value, ajax: 1}, function (response, textstatus, xhr) {
                if (xhr.status == 200) {
                    if(value.length==10) {
                        $('#registration-icon').addClass('text-amber-500 dark:text-amber-400');
                        $('#registration-icon').removeClass('text-red-600 dark:text-red-400 text-green-600 dark:text-green-400');
                        $('#registration-icon').html('<path d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495ZM10 5a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 5Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" fill-rule="evenodd"></path>');
                        $('#registration-message').removeClass('text-red-500 text-green-500');
                        $('#registration-message').addClass('text-amber-500');
                        $('#registration-message').html(response.message);
                    } else if(response.success==true) {
                        $('#registration-icon').addClass('text-green-600 dark:text-green-400');
                        $('#registration-icon').removeClass('text-red-600 dark:text-red-400 text-amber-500 dark:text-amber-400');
                        $('#registration-icon').html('<path d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" fill-rule="evenodd"></path>');
                        $('#registration-message').removeClass('text-amber-500 text-red-500');
                        $('#registration-message').addClass('text-green-500');
                        $('#registration-message').html(response.message);
                    } else {
                        $('#registration-icon').html('<path d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM8.28 7.22a.75.75 0 0 0-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 1 0 1.06 1.06L10 11.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L11.06 10l1.72-1.72a.75.75 0 0 0-1.06-1.06L10 8.94 8.28 7.22Z" clip-rule="evenodd" fill-rule="evenodd"></path>');
                        $('#registration-icon').addClass('text-red-600 dark:text-red-400');
                        $('#registration-icon').removeClass('text-amber-500 dark:text-amber-400 text-green-600 dark:text-green-400');
                        $('#registration-message').removeClass('text-amber-500 text-green-500');
                        $('#registration-message').addClass('text-red-500');
                        $('#registration-message').html(response.message);
                    }
                }
            });
        }
    }

    const dropZone = document.getElementById("drop-zone");
    $('#drop-zone').on('drop', function(event) {
        dropHandler(event, $('#drop-zone').data('type'));
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

    const fileTypesImages = [
        "image/jpeg",
        "image/jpg",
        "image/png",
        "image/webp",
        "image/svg+xml",
    ];
    const fileTypesDocuments = [
        "image/jpeg",
        "image/jpg",
        "image/png",
        "image/webp",
        "image/svg+xml",
        "application/pdf",
        "application/vnd.ms-excel",
        "application/msword",
        "application/vnd.ms-powerpoint",
        "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
        "application/vnd.openxmlformats-officedocument.presentationml.presentation",
        "application/zip",
        "application/x-zip-compressed",
    ];

    function validFileType(file, type) {
        if(type=='file') {
            return fileTypesDocuments.includes(file.type);
        } else {
            return fileTypesImages.includes(file.type);
        }
    }
    function dropHandler(event, type) {
        // Prevent default behavior (Prevent file from being opened)
        event.preventDefault();
        let result = "";
        dropZone.classList.remove('bg-gray-100');
        // Use DataTransferItemList interface to access the file(s)
        [...event.originalEvent.dataTransfer.items].forEach((item, i) => {
            // If dropped items aren't files, reject them
            if (item.kind === "file") {
                const file = item.getAsFile();
                if (validFileType(file, type)) {
                    document.getElementById(('filetype-error')).classList.add('hidden');
                    document.getElementById(('drop-zone')).classList.remove('border-red-600','dark:border-red-400');
                    if(type!="file") {
                        const src = URL.createObjectURL(file);
                        const image = document.createElement("img");
                        image.classList.add('mx-auto', 'size-12', 'text-gray-300', 'dark:text-gray-500');
                        image.src = src;
                        document.getElementById(('preview-icon')).remove();
                        document.getElementById('form_image').files = event.originalEvent.dataTransfer.files;
                        document.getElementById('icon-holder').append(image);
                    } else {
                        document.getElementById('form_file').files = event.originalEvent.dataTransfer.files;
                        $('#icon-holder').html(file.name)
                    }
                    console.log(document.getElementById('form_file').value);

                } else {
                    document.getElementById(('filetype-error')).classList.remove('hidden');
                    document.getElementById(('drop-zone')).classList.add('border-red-600','dark:border-red-400');
                }
            }
        });
    }
});
