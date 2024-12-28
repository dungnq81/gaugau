import Ensemble from 'ensemble-social-share/dist/js/ensemble-social-share.min';

export const initializeSocialShare = ( attributeName = 'social-share', customOptions = {} ) => {
    const defaultOptions = {
        displays: ['facebook', 'ex', 'whatsapp', 'messenger', 'telegram', 'linkedin', 'send-email', 'copy-link', 'web-share'],
    };

    const options = { ...defaultOptions, ...customOptions };
    const elements = document.querySelectorAll( `[${attributeName}]` );

    if ( elements.length === 0 ) {
        console.warn( `No elements found with attribute: ${attributeName}` );
        return null;
    }

    const instances = [];
    elements.forEach( ( element ) => {
        const instance = new Ensemble.SocialShare( element, options );
        instances.push( instance );
    } );

    return instances;
};
