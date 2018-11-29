<?php /* Markup for a single block when inserted into the DOM */ ?>
<script type="text/html" id="tmpl-repeat-block">
<div class="single_block">
    <a href="javascript:void(0)" class="remove_block">X</a>
    <div class="inner_block">
        <div class="fields">
            <select name="course_fields[]" class="course_select">
                <option value="">Select Course</option>
                <option value="MCA">MCA</option>
                <option value="BCA">BCA</option>
                <option value="BTECH">BTECT</option> 
            </select>
        </div>
        <div class="fields">
            <input type="text" name="stu_name[]" value="" Placeholder="Student Name" class="stu_name">
        </div>
    </div>
</div>  
<!-- #comment-## -->
</script>