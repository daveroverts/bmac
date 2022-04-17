// TinyMCE
import tinymce from 'tinymce';
import 'tinymce/plugins/code';
import 'tinymce/plugins/link';

tinymce.init({
    selector: 'textarea.tinymce',
    plugins: ['code', 'link']
});
