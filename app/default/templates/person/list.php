<?php

    namespace Runtime\App\Template;

    $a_person = $this->a_template['a_person'];
?>

<h1>Seznam všech žáků</h1>

<table class="table table-hover">
    <?php
    if(is_array($a_person) && count($a_person)) {
        ?>
        <tr>
            <th>Číslo žáka</th>
            <th>Jméno a příjmení</th>
            <th>Pohlaví</th>
            <th>Věk</th>
            <th>Zájmové kroužky</th>
        </tr>
        <?php
        foreach($a_person as $o_person) {
            ?>
            <tr>
                <td><?=$o_person->id_person;?></td>
                <td><a href="/person/detail?id_child=<?=$o_person->id_person;?>" data-id="load_person" data-item="<?=$o_person->id_person;?>"><?=$o_person->s_name;?> <?=$o_person->s_lastname;?></a></td>
                <td><?=$o_person->e_gender;?></td>
                <td><?=$o_person->nl_age;?></td>
                <td><?=$o_person->hobby_group_name;?></td>
            </tr>
            <?php
        }
    } else {
        ?>
        <tr>
            <td>Nebyli nalezeni žádní žáci</td>
        </tr>
        <?php
    }
    ?>
</table>

<script type="text/javascript">

    $('*[data-id="load_person"]').click(function(e) {
        var id_child = $(this).attr('data-item');
        $.ajax( {
            url: '/person/detail',
            data: {
                b_ajax: true,
                id_child: id_child
            },
            success: function(response, status) {
                $('*[data-id="content"]').html(response);
            }
        });
        e.preventDefault();
    })

</script>
