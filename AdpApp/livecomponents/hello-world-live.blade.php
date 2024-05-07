@set($postObj=adpGetSinglePost(67))

<h3>{{$postObj['Post']->post_title}}</h3>

<div class="postContent">{!!$postObj['Post']->post_content!!}</div>