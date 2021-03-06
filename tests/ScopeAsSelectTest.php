<?php

namespace ProtoneMedia\LaravelEloquentScopeAsSelect\Tests;

class ScopeAsSelectTest extends TestCase
{
    /** @test */
    public function it_can_add_a_scope_as_a_select()
    {
        $postA = Post::create(['title' => 'foo']);
        $postB = Post::create(['title' => 'bar']);

        $posts = Post::query()
            ->addScopeAsSelect('title_is_foo', fn ($query) => $query->titleIsFoo())
            ->orderBy('id')
            ->get();

        $this->assertTrue($posts->get(0)->title_is_foo);
        $this->assertFalse($posts->get(1)->title_is_foo);
    }

    /** @test */
    public function it_can_add_multiple_and_has_relation_scopes()
    {
        $postA = Post::create(['title' => 'foo']);
        $postB = Post::create(['title' => 'bar']);

        foreach (range(1, 5) as $i) {
            $postA->comments()->create(['body' => 'ok']);
        }

        foreach (range(1, 10) as $i) {
            $postB->comments()->create(['body' => 'ok']);
        }

        $posts = Post::query()
            ->addScopeAsSelect('title_is_foo', function ($query) {
                $query->titleIsFoo();
            })
            ->addScopeAsSelect('has_six_or_more_comments', function ($query) {
                $query->hasSixOrMoreComments();
            })
            ->orderBy('id')
            ->get();

        $this->assertTrue($posts->get(0)->title_is_foo);
        $this->assertFalse($posts->get(1)->title_is_foo);

        $this->assertFalse($posts->get(0)->has_six_or_more_comments);
        $this->assertTrue($posts->get(1)->has_six_or_more_comments);
    }

    /** @test */
    public function it_can_do_inline_contraints_as_well()
    {
        $postA = Post::create(['title' => 'foo']);
        $postB = Post::create(['title' => 'foo']);
        $postC = Post::create(['title' => 'bar']);
        $postD = Post::create(['title' => 'bar']);

        foreach (range(1, 5) as $i) {
            $postA->comments()->create(['body' => 'ok']);
            $postC->comments()->create(['body' => 'ok']);
        }

        foreach (range(1, 10) as $i) {
            $postB->comments()->create(['body' => 'ok']);
            $postD->comments()->create(['body' => 'ok']);
        }

        $posts = Post::query()
            ->addScopeAsSelect('title_is_foo_and_has_six_comments_or_more', function ($query) {
                $query->where('title', 'foo')->has('comments', '>=', 6);
            })
            ->orderBy('id')
            ->get();

        $this->assertFalse($posts->get(0)->title_is_foo_and_has_six_comments_or_more);
        $this->assertTrue($posts->get(1)->title_is_foo_and_has_six_comments_or_more);
        $this->assertFalse($posts->get(2)->title_is_foo_and_has_six_comments_or_more);
        $this->assertFalse($posts->get(3)->title_is_foo_and_has_six_comments_or_more);
    }
}
