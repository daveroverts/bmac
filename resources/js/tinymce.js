// TinyMCE
import tinymce from 'tinymce';
import 'tinymce/plugins/code';
import 'tinymce/plugins/link';

tinymce.init({
    selector: 'textarea.tinymce',
    plugins: ['code', 'link'],
    base_url: '/build/js',
    license_key: 'gpl',
});
