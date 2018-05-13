<?php

if (!defined('GNUSOCIAL')) {
    exit(1);
}

class Nodeinfo_2_0Action extends ApiAction
{
    protected function handle()
    {
        parent::handle();

        $this->showNodeInfo();
    }

    /*
     * Technically, the NodeInfo spec defines 'active' as 'signed in at least once',
     * but GNU social doesn't keep track of when users last logged in, so let's return
     * the number of users that 'posted at least once', I guess.
     */
    function getActiveUsers($days)
    {
        $notices = new Notice();
        $notices->joinAdd(array('profile_id', 'user:id'));
        $notices->whereAdd('notice.created >= NOW() - INTERVAL ' . $days . ' DAY');

        $activeUsersCount = $notices->count('distinct profile_id');

        return $activeUsersCount;
    }

    function getRegistrationsStatus()
    {
        $areRegistrationsClosed = (common_config('site', 'closed')) ? true : false;
        $isSiteInviteOnly = (common_config('site', 'inviteonly')) ? true : false;

        return !($areRegistrationsClosed || $isSiteInviteOnly);
    }

    function getUserCount()
    {
        $users = new User();
        $userCount = $users->count();

        return $userCount;
    }

    function getPostCount()
    {
        $notices = new Notice();
        $notices->is_local = Notice::LOCAL_PUBLIC;
        $notices->whereAdd('reply_to IS NULL');
        $noticeCount = $notices->count();

        return $noticeCount;
    }

    function getCommentCount()
    {
        $notices = new Notice();
        $notices->is_local = Notice::LOCAL_PUBLIC;
        $notices->whereAdd('reply_to IS NOT NULL');
        $commentCount = $notices->count();

        return $commentCount;
    }

    function showNodeInfo()
    {
        $openRegistrations = $this->getRegistrationsStatus();
        $userCount = $this->getUserCount();
        $postCount = $this->getPostCount();
        $commentCount = $this->getCommentCount();

        $usersActiveHalfyear = $this->getActiveUsers(180);
        $usersActiveMonth = $this->getActiveUsers(30);

        $json = json_encode([
            'version' => '2.0',

            'software' => [
                'name' => 'gnusocial',
                'version' => GNUSOCIAL_VERSION
            ],

            // TODO: Have plugins register protocols
            //       (ostatus, xmpp are plugins and may not be enabled)
            'protocols' => ['ostatus'],

            // TODO: Have plugins register services
            'services' => [
                'inbound' => ['atom1.0', 'gnusocial', 'rss2.0'],
                'outbound' => ['atom1.0', 'gnusocial', 'rss2.0']
            ],

            'openRegistrations' => $openRegistrations,

            'usage' => [
                'users' => [
                    'total' => $userCount,
                    'activeHalfyear' => $usersActiveHalfyear,
                    'activeMonth' => $usersActiveMonth
                ],
                'localPosts' => $postCount,
                'localComments' => $commentCount
            ],

            'metadata' => new stdClass()
        ]);

        $this->initDocument('json');
        print $json;
        $this->endDocument('json');
    }
}

