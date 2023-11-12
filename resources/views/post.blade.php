<form action="/post" method="post">
    @csrf
    <label for="title">標題:</label>
    <input type="text" id="title" name="title"><br><br>
    <label for="content">內容:</label>
    <textarea id="content" name="content"></textarea><br><br>
    <input type="submit" value="提交">
</form>
