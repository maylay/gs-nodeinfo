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
                    'activeHalfyear' => 1, // TODO
                    'activeMonth' => 1 // TODO
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

