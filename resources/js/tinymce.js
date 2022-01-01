// TinyMCE
import tinymce from 'tinymce';
import 'tinymce/icons/default';
import 'tinymce/themes/silver';
import 'tinymce/plugins/code';
import 'tinymce/plugins/paste';
import 'tinymce/plugins/link';

tinymce.init({
    selector: 'textarea.tinymce',
    plugins: ['code', 'paste', 'link']
});
