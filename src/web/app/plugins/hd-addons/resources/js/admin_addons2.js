import { nanoid } from 'nanoid';
import Cookies from 'js-cookie';

Object.assign(window, { Cookies });

jQuery(function ($) {

    // random id
    function rand_element_init( el ) {
        const $el = $( el );
        const _rand = nanoid( 9 );
        $el.addClass( _rand );

        let _id = $el.attr( 'id' );
        if ( !_id ) {
            _id = _rand;
            $el.attr( 'id', _id );
        }

        return _id;
    }

    // codemirror
    if ( typeof codemirror_settings !== 'undefined' ) {
        const codemirror_css = [ ...document.querySelectorAll( '.codemirror_css' ) ];
        const codemirror_html = [ ...document.querySelectorAll( '.codemirror_html' ) ];

        function initializeCodeMirror( elements, settings, type ) {
            elements.forEach( function ( el ) {
                if ( !el.CodeMirror ) {
                    console.log( `Initializing CodeMirror for ${ type } on element:`, el );
                    rand_element_init( el );
                    let editorSettings = settings ? { ...settings } : {};
                    editorSettings.codemirror = {
                        ...editorSettings.codemirror,
                        indentUnit: 3,
                        tabSize: 3,
                        autoRefresh: true,
                    };
                    el.CodeMirror = wp.codeEditor.initialize( el, editorSettings );
                } else {
                    console.log( `CodeMirror already initialized for ${ type } on element:`, el );
                }
            } );
        }

        initializeCodeMirror( codemirror_css, codemirror_settings.codemirror_css, 'CSS' );
        initializeCodeMirror( codemirror_html, codemirror_settings.codemirror_html, 'HTML' );
    }

    $.fn.fadeOutAndRemove = function (speed) {
        return this.fadeOut(speed, function () {
            $(this).remove();
        });
    };

    $.fn.serializeObject = function () {
        let obj = {};
        let array = this.serializeArray();

        $.each(array, function () {
            let name = this.name;
            let value = this.value || '';

            // Check if the name ends with []
            if (name.indexOf('[]') > -1) {
                name = name.replace('[]', '');

                // Ensure the object property is an array
                if (!obj[name]) {
                    obj[name] = [];
                }

                // Push the value into the array
                obj[name].push(value);
            } else {
                // Check if the object already has a property with the given name
                if (obj[name] !== undefined) {
                    if (!Array.isArray(obj[name])) {
                        obj[name] = [obj[name]];
                    }
                    obj[name].push(value);
                } else {
                    obj[name] = value;
                }
            }
        });

        return obj;
    };

    // hide notice
    $(document).on('click', '.notice-dismiss', function (e) {
        $(this).closest('.notice.is-dismissible')?.fadeOutAndRemove(400);
    });

    // ajax submit settings
    $(document).on('submit', '#_settings_form', function (e) {
        e.preventDefault();
        let $this = $(this);

        let btn_submit = $this.find('button[name="_submit_settings"]');
        let button_text = btn_submit.html();
        //let button_text_loading = '<i class="fa-solid fa-spinner fa-spin-pulse"></i>';
        let button_text_loading = '<span class="ajax-loader">&nbsp;</span>';

        btn_submit.prop('disabled', true).html(button_text_loading);
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'submit_settings',
                _data: $this.serializeObject(),
                _ajax_nonce: $this.find('input[name="_wpnonce"]').val(),
                _wp_http_referer: $this.find('input[name="_wp_http_referer"]').val(),
            },
        })
        .done(function (data) {
            btn_submit.prop('disabled', false).html(button_text);
            $this.find('#_content').prepend(data);

            // dismissible auto hide
            setTimeout(() => {
                $this.find('#_content').find('.dismissible-auto')?.fadeOutAndRemove(400);
            }, 4000);
        })
        .fail(function (jqXHR, textStatus, errorThrown) {
            btn_submit.prop('disabled', false).html(button_text);
            console.log(errorThrown);
        });
    });

    // filter tabs
    const filter_tabs = $('.filter-tabs');
    $.each(filter_tabs, function (i, el) {
        const $el = $(el),
            _id = rand_element_init(el),
            $nav = $el.find('.tabs-nav'),
            $content = $el.find('.tabs-content');

        const _cookie = `cookie_${_id}_${i}`;
        let cookieValue = Cookies.get(_cookie);

        if (!cookieValue) {
            cookieValue = $nav.find('a:first').attr('href');
            Cookies.set(_cookie, cookieValue, { expires: 7, path: '', secure: true });
        }
        $nav.find('a.current').removeClass('current');
        $nav.find(`a[href="${cookieValue}"]`).addClass('current');
        $nav.find('a')
            .on('click', function (e) {
                e.preventDefault();

                const $this = $(this);
                const hash = $this.attr('href');
                Cookies.set(_cookie, hash, { expires: 7, path: '', secure: true });

                $nav.find('a.current').removeClass('current');
                $content.find('.tabs-panel:visible').removeClass('show')?.hide();

                $($this.attr('href')).addClass('show')?.show();
                $this.addClass('current');
            })
            .filter('.current')
            .trigger('click');
    });

    // select2 multiple
    const select2_multiple = $( '.select2-multiple' );
    $.each( select2_multiple, function ( i, el ) {
        $( el ).select2( {
            multiple: true,
            allowClear: true,
            width: 'resolve',
            dropdownAutoWidth: true,
            placeholder: $( el ).attr( 'placeholder' ),
        } );
    } );

    // select2 tags
    const select2_tags = $( '.select2-tags' );
    $.each( select2_tags, function ( i, el ) {
        $( el ).select2( {
            multiple: true,
            tags: true,
            allowClear: true,
            width: 'resolve',
            dropdownAutoWidth: true,
            placeholder: $( el ).attr( 'placeholder' ),
        } );
    } );

    // select2 IPs
    const select2_ips = $( '.select2-ips' );
    $.each( select2_ips, function ( i, el ) {
        $( el ).select2( {
            multiple: true,
            tags: true,
            allowClear: true,
            width: 'resolve',
            dropdownAutoWidth: true,
            placeholder: $( el ).attr( 'placeholder' ),
            createTag: function ( params ) {
                let term = $.trim( params.term );

                // Validate the term as an IP address or range
                if ( isValidIPRange( term ) ) {
                    return {
                        id: term, text: term,
                    };
                } else {
                    return null;
                }
            },
        } );
    } );
});

/**
 * validate IP range (IPv4)
 *
 * @param range
 * @returns {boolean}
 */
function isValidIPRange( range ) {
    const ipPattern = /^(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})\.(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})\.(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})\.(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})$/;
    const rangePattern = /^(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})\.(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})\.(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})\.(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})-(\d|[1-9]\d|1\d{2}|2[0-4]\d|25[0-5])$/;
    const cidrPattern = /^(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})\.(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})\.(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})\.(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})\/(\d|[1-2]\d|3[0-2])$/;

    if ( ipPattern.test( range ) ) {
        return true;
    }

    if ( rangePattern.test( range ) ) {
        const [ startIP, endRange ] = range.split( '-' );
        const endIP = startIP.split( '.' ).slice( 0, 3 ).join( '.' ) + '.' + endRange;
        return compareIPs( startIP, endIP ) < 0;
    }

    return cidrPattern.test( range );
}

/**
 * compare two IP addresses
 *
 * @param ip1
 * @param ip2
 * @returns {number}
 */
function compareIPs( ip1, ip2 ) {
    const ip1Parts = ip1.split( '.' ).map( Number );
    const ip2Parts = ip2.split( '.' ).map( Number );

    for ( let i = 0; i < 4; i++ ) {
        if ( ip1Parts[i] < ip2Parts[i] ) return -1;
        if ( ip1Parts[i] > ip2Parts[i] ) return 1;
    }
    return 0;
}
