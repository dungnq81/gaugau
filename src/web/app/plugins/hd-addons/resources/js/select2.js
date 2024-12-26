jQuery( function ( $ ) {
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
                        id: term,
                        text: term,
                    };
                } else {
                    return null;
                }
            },
        } );
    } );

    // select2 emails
    const select2_emails = $( '.select2-emails' );
    $.each( select2_emails, function ( i, el ) {
        $( el ).select2( {
            multiple: true,
            tags: true,
            allowClear: true,
            width: 'resolve',
            dropdownAutoWidth: true,
            placeholder: $( el ).attr( 'placeholder' ),
            createTag: function ( params ) {
                let term = $.trim( params.term );
                if ( isValidEmail( term ) ) {
                    return {
                        id: term,
                        text: term,
                    };
                } else {
                    return null;
                }
            },
        } );
    } );
} );

/**
 * Validate email address
 *
 * @param {string} email
 * @returns {boolean}
 */
function isValidEmail( email ) {
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailPattern.test( email );
}

/**
 * Validate phone number
 *
 * @param phone
 * @returns {boolean}
 */
function isValidPhone( phone ) {
    if ( typeof phone !== 'string' || phone.trim() === '' ) {
        return false;
    }

    const pattern = /^\(?\+?(0|84?)\)?[\s.-]?(3[2-9]|5[689]|7[06-9]|8[0-689]|9[0-4|6-9])(\d{7}|\d[\s.-]?\d{3}[\s.-]?\d{3})$/;

    return pattern.test( phone );
}

/**
 * validate IP range (IPv4)
 *
 * @param range
 * @returns {boolean}
 */
function isValidIPRange( range ) {
    const ipPattern =
        /^(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})\.(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})\.(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})\.(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})$/;
    const rangePattern =
        /^(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})\.(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})\.(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})\.(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})-(\d|[1-9]\d|1\d{2}|2[0-4]\d|25[0-5])$/;
    const cidrPattern =
        /^(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})\.(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})\.(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})\.(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})\/(\d|[1-2]\d|3[0-2])$/;

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
