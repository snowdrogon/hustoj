<ul class="pagination problem-pagination">
    <?php for($i = 1; $i <= $pages; $i++): ?>
        <li<?php if($i == Request::$current->param('id')):?> class="active"<?php endif;?>><a href="/admin/problem/list/<?php echo($i);?>"><?php echo($i);?></a></li>
    <?php endfor;?>
</ul>
<table class="table table-striped">
    <thead>
    <tr><th>Problem Id</th><th>Title</th><th>Add Date</th><th>Defunct</th><th>OP</th></tr>
    </thead>
<?php /* @var Model_Problem[] $problem_list */ foreach($problem_list as $p):?>
<tr>
<td><?php echo $p->problem_id;?></td>
<td><?php echo $p->title;?></td>
<td><?php echo($p->in_date);?></td>
<td><a id="defunct-<?php echo($p->problem_id);?>" class="dp btn" data-value="<?php echo $p->problem_id;?>"><?php echo($p->defunct);?></a></td>
<td><a class="edit-link" href="/admin/problem/edit/<?php echo $p->problem_id;?>">[Edit]</a></td>
</tr>
<?php endforeach;?>
</table>
<script type="text/javascript">
    function check_defunct(problem_id)
    {
        var elem = $('#defunct-' + problem_id);
        if (elem.html() == 'Y')
        {
            elem.removeClass('btn-success');
            elem.addClass('btn-danger');
        } else {
            elem.removeClass('btn-danger');
            elem.addClass('btn-success');
        }
    }
    $(function(){
       $('.dp').each(function(){
           var problem_id = $(this).attr('data-value');
           check_defunct(problem_id);
       })
    });
    $('a.dp').click(function(){
        var problem_id = $(this).attr('data-value');
        console.log(problem_id);
        var user_ok = confirm('Are u sure change this problem defucnt ?');
        if (user_ok)
        {
            var url = '/admin/problem/defunct'
            $.getJSON(url, {'problem_id': problem_id}, function(response){
                console.log(response);
                var elem = $('#defunct-' + problem_id);
                elem.html(response.result)

                check_defunct(problem_id);
            })
        }
    });
</script>