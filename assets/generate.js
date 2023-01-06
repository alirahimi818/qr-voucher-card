jQuery(".input-code").on('keyup', function () {
    find_qr_voucher_card(jQuery(this).val())
})

jQuery(".qr-generate-page .qrvc-btn.minus").click(function () {
    let price_symbol = jQuery(this).attr('data-symbol');
    let price_slider_el = jQuery(".qr-generate-page .qr-price-slider");
    let price_el = jQuery(".qr-generate-page .qr-price-amount");
    let price = price_el.val();
    price = isNaN(price) ? price.substring(1) : price;
    price = price >= 0.1 ? (price - 0.1).toFixed(2) : price;
    price_el.val(price_symbol + price);
    price_slider_el.slider({value: Number(price)})
})

jQuery(".qr-generate-page .qrvc-btn.plus").click(function () {
    let price_symbol = jQuery(this).attr('data-symbol');
    let price_max = Number(jQuery(this).attr('data-max'));
    let price_slider_el = jQuery(".qr-generate-page .qr-price-slider");
    let price_el = jQuery(".qr-generate-page .qr-price-amount");
    let price = price_el.val();
    price = isNaN(price) ? price.substring(1) : price;
    price = (Number(price) + 0.1).toFixed(2);
    if (price > price_max) {
        price = price_max;
    }
    price_el.val(price_symbol + price);
    price_slider_el.slider({value: Number(price)})
})

jQuery(".qr-generate-page .save-qr-btn").click(function () {
    let price = jQuery(".qr-generate-page .qr-price-amount").val();
    let code = jQuery(".qr-generate-page .qr-voucher-code").val();
    price = isNaN(price) ? price.substring(1) : price;
    if (price > 0) {
        jQuery(this).attr('disabled', 'disabled');
        update_qr_voucher_card(code, price)
    }
})

jQuery(".qr-generate-page .new-qr-btn").click(function () {
    let price = jQuery(".qr-generate-page .qr-price-amount").val();
    price = isNaN(price) ? price.substring(1) : price;
    generate_qr_voucher_card(price)
})

jQuery(".qr-generate-page .qr-default-price-btn").click(function () {
    let price = jQuery(this).attr('data-price');
    let slider = jQuery(".qr-price-slider");
    slider.slider({value: price})
    jQuery(".qr-price-amount").val(slider.attr('data-symbol') + Number(price).toFixed(slider.attr('data-decimal')));
})

jQuery(function () {
    let slider_el = jQuery(".qr-price-slider");
    slider_el.slider({
        value: Number(slider_el.attr('data-default-value')),
        min: 0,
        max: Number(slider_el.attr('data-max')),
        step: Number(slider_el.attr('data-step')),
        slide: function (event, ui) {
            jQuery(".qr-price-amount").val(slider_el.attr('data-symbol') + (ui.value).toFixed(Number(slider_el.attr('data-decimal'))));
        }
    });
    jQuery(".qr-price-amount").val(slider_el.attr('data-symbol') + Number(slider_el.slider("value")).toFixed(Number(slider_el.attr('data-decimal'))));
});

function qrvc_print_qr_code() {
    let divContents = jQuery(".barcode-img-area").clone();
    let body = jQuery("body").detach();
    document.body = document.createElement("body");
    divContents.appendTo(jQuery("body"));
    setTimeout(function () {
        window.print();
    }, 1000)
    jQuery("html body").remove();
    body.appendTo(jQuery("html"));
}

function generate_qr_voucher_card(price) {
    let img_el = jQuery('.qr-generate-page .barcode-img-area img');
    img_el.addClass('loading')
    let xhr = new XMLHttpRequest();
    xhr.open("POST", `${window.location.origin}/wp-admin/admin-ajax.php`, true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    let params = 'action=generate_qr_voucher_card&price=' + price;
    xhr.onreadystatechange = () => {

        if (xhr.readyState == 4) {
            if (xhr.status == 200) {

                try {
                    let obj = JSON.parse(xhr.responseText);
                    setTimeout(function () {
                        img_el.removeClass('loading')
                    }, 2500)
                    img_el.attr('src', obj.url)
                    jQuery('.qr-generate-page .barcode-area').text(obj.code)
                    jQuery('.qr-generate-page .barcode-date-area').text(obj.date)
                    jQuery('.qr-generate-page .qr-print-btn').css('display', 'block')
                } catch (e) {
                    console.log(e);
                }
            } else {
                console.log("SOME ERROR HTTP");
                console.log(xhr.responseText);

            }

        }
    };

    xhr.send(params);
}

function update_qr_voucher_card(code, price) {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", `${window.location.origin}/wp-admin/admin-ajax.php`, true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    let params = 'action=update_qr_voucher_card&code=' + code + '&price=' + price;
    xhr.onreadystatechange = () => {

        if (xhr.readyState == 4) {
            if (xhr.status == 200) {

                try {
                    let obj = JSON.parse(xhr.responseText);
                    jQuery('.qr-generate-page .qr-voucher-update-success-message').text(obj.message)
                    setTimeout(function () {
                        window.location.reload();
                    }, 3000)
                } catch (e) {
                    console.log(e);
                }
            } else {
                console.log("SOME ERROR HTTP");
                console.log(xhr.responseText);

            }

        }
    };

    xhr.send(params);
}

function find_qr_voucher_card(code) {

    jQuery(".input-code").autocomplete({
        source:
            function (request, response) {
                jQuery.getJSON(`${window.location.origin}/wp-admin/admin-ajax.php?action=find_qr_voucher_card&code=${code}`, function (data) {
                    jQuery(".input-code-hidden").val(data[0].code);
                    response(jQuery.map(data, function (item) {
                        return {
                            label: item.code,
                            value: item.code
                        };
                    }));
                });
            },
        classes: {
            "ui-autocomplete": "qr-find-autocomplete"
        },
        dataType: "jsonp",
        minLength: 2,
        select: function (event, ui) {
            jQuery(".input-code").val(ui.item.value);
            jQuery(".qr-find-form").submit();
        }
    }).on("keyup", function (event) {
        jQuery(".input-code-hidden").val('');
    });
}