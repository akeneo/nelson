<?php

namespace Akeneo\Event;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Events
{
    // Github
    const PRE_GITHUB_CLONE = 'pre_github_clone';

    const POST_GITHUB_CLONE = 'post_github_clone';

    const PRE_GITHUB_SET_BRANCH = 'pre_github_set_branch';

    const POST_GITHUB_SET_BRANCH = 'post_github_set_branch';

    const PRE_GITHUB_UPDATE = 'pre_github_update';

    const POST_GITHUB_UPDATE = 'post_github_update';

    const PRE_GITHUB_CREATE_PR = 'pre_github_create_pr';

    const POST_GITHUB_CREATE_PR = 'post_github_create_pr';

    // Crowdin
    const PRE_CROWDIN_EXPORT = 'pre_crowdin_export';

    const POST_CROWDIN_EXPORT = 'post_crowdin_export';

    const PRE_CROWDIN_DOWNLOAD = 'pre_crowdin_download';

    const POST_CROWDIN_DOWNLOAD = 'post_crowdin_download';

    const CROWDIN_CREATE_BRANCH = 'crowdin_create_branch';

    const PRE_CROWDIN_CREATE_DIRECTORIES = 'pre_crowdin_create_directories';

    const CROWDIN_CREATE_DIRECTORY = 'crowdin_create_directory';

    const POST_CROWDIN_CREATE_DIRECTORIES = 'post_crowdin_create_directories';

    const PRE_CROWDIN_CREATE_FILES = 'pre_crowdin_create_files';

    const CROWDIN_CREATE_FILE =  'crowdin_create_file';

    const POST_CROWDIN_CREATE_FILES = 'post_crowdin_create_files';

    const PRE_CROWDIN_UPDATE_FILES = 'pre_crowdin_update_files';

    const CROWDIN_UPDATE_FILE = 'crowdin_update_file';

    const POST_CROWDIN_UPDATE_FILES = 'post_crowdin_update_files';

    // Nelson
    const PRE_NELSON_PUSH = 'pre_nelson_push';

    const POST_NELSON_PUSH = 'post_nelson_push';

    const PRE_NELSON_PULL = 'pre_nelson_pull';

    const POST_NELSON_PULL = 'post_nelson_pull';
}
