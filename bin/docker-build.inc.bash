#!/usr/bin/env bash

set -eu

function buildDockerImage() {
    local dockerImageName="${1}"
    local dockerFilePath="${2}"

    if [ "${refresh}" == true ]; then
        local refreshArguments="--no-cache --pull"
    else
        local refreshArguments=
    fi

    readonly GROUP_DOCKER_ID=$(getent group docker | awk -F: '{printf "%d\n", $3}')

    DOCKER_BUILDKIT=1 \
        docker \
            build \
                --file "${dockerFilePath}" \
                --tag="${dockerImageName}" \
                --build-arg USER_APP_ID="$(id -u)" \
                --build-arg GROUP_APP_ID="$(id -g)" \
                --build-arg GROUP_DOCKER_ID="${GROUP_DOCKER_ID}" \
                ${refreshArguments} \
                "${ROOT_DIR}"
}

function pushDockerImage() {
    local dockerImageName="${1}"

    echo "Push Docker image ${dockerImageName}."
    docker push "${dockerImageName}"
}

refresh=false
push=false
for param in "${@}"; do
    if [ "${param}" == "--refresh" ]; then
        refresh=true
    elif [ "${param}" == "--push" ]; then
        push=true
    fi
done

buildDockerImage "${DOCKER_IMAGE_NAME}" "${DOCKER_FILE_PATH}"

if [ "${push}" == true ]; then
    pushDockerImage "${DOCKER_IMAGE_NAME}"
fi
