#!/usr/bin/env bash

set -eu

readonly DOCKER_CI_IMAGE_NAME="steevanb/symfony-container-introspection:ci"
readonly DOCKER_RELEASE_IMAGE_NAME="steevanb/symfony-container-introspection:release"
readonly DOCKER_SYMFONY_START_IMAGE_NAME="steevanb/symfony-container-introspection:symfony-start"
