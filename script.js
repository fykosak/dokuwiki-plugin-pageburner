jQuery(function () {
    var $ = jQuery;
    $('#page-burner_template-page').each(function () {
        const $form = $(this).parents('form');
        const data = JSON.parse($(this).attr('data-data'));
        $(this).change(function () {
            const name = $(this).find('option:selected').first().val();
            const selected = data.filter(function (template) {
                return template.name == name;
            });
            if (selected && selected[0]) {
                $form.find('[name="template"]').val(selected[0].template);
                $form.find('[name="page_path"]').val(selected[0].page_path);
            }
        });
    });
});
