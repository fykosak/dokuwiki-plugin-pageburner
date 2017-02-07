jQuery(function () {
    var $ = jQuery;
    $('#page-burner_template-page').each(function () {
        var $form = $(this).parents('form');
        console.log($form);
        const data = JSON.parse($(this).attr('data-data'));
        console.log(data);
        $(this).change(function () {
            console.log($(this).find('option:selected'));
            console.log($(this).find('option:selected').first());
            var name = $(this).find('option:selected').first().val();
            console.log(name);
            var selected = data.filter(function (template) {
                return template.name == name;
            });
            if (selected && selected[0]) {

                $form.find('[name="template"]').val(selected[0].template);
                $form.find('[name="page_path"]').val(selected[0].page_path);
                console.log(selected[0]);
            }

        });

    });

});