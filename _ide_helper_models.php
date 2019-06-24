<?php

// @formatter:off
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App{
/**
 * App\User
 *
 * @property int $id
 * @property string|null $screen_name
 * @property string|null $last_crawl_time
 * @property int|null $last_tweet_id
 * @property string|null $status
 * @property string|null $grade
 * @property string|null $profile_review
 * @property int|null $last_liked_tweet_id
 * @property string|null $last_crawl_liked_time
 * @property string|null $lang
 * @property int|null $error_code
 * @property string|null $error_description
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereErrorCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereErrorDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereGrade($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereLang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereLastCrawlLikedTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereLastCrawlTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereLastLikedTweetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereLastTweetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereProfileReview($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereScreenName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereStatus($value)
 */
	class User extends \Eloquent {}
}

namespace App{
/**
 * App\TwitterUser
 *
 * @property int $id
 * @property string|null $screen_name
 * @property string|null $last_crawl_time
 * @property int|null $last_tweet_id
 * @property string|null $status
 * @property string|null $grade
 * @property string|null $profile_review
 * @property int|null $last_liked_tweet_id
 * @property string|null $last_crawl_liked_time
 * @property string|null $lang
 * @property int|null $error_code
 * @property string|null $error_description
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwitterUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwitterUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwitterUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwitterUser whereErrorCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwitterUser whereErrorDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwitterUser whereGrade($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwitterUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwitterUser whereLang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwitterUser whereLastCrawlLikedTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwitterUser whereLastCrawlTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwitterUser whereLastLikedTweetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwitterUser whereLastTweetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwitterUser whereProfileReview($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwitterUser whereScreenName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwitterUser whereStatus($value)
 */
	class TwitterUser extends \Eloquent {}
}

