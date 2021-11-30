#!/usr/bin/env bash

set -eu

if [ -z "${BIN_DIR-}" ]; then
    BIN_DIR="bin"
fi

if [ -z "${DOCKER_IMAGE_NAME-}" ]; then
    echo "You need to define \$DOCKER_IMAGE_NAME."
    exit 1
fi

if [ -z "${I_AM_A_DOCKER_CONTAINER:-}" ]; then
    set +e
    tty -s && isInteractiveShell=true || isInteractiveShell=false
    set -e

    if ${isInteractiveShell}; then
        interactiveParameter="--interactive"
    else
        interactiveParameter=
    fi

    docker \
        run \
            --rm \
            --tty \
            ${interactiveParameter} \
            --volume "${ROOT_DIR}":/app \
            --volume /usr/bin/docker:/usr/bin/docker \
            --volume /var/run/docker.sock:/var/run/docker.sock \
            --volume /usr/bin/docker-compose:/usr/bin/docker-compose \
            --volume /usr/bin/docker-compose-v1:/usr/bin/docker-compose-v1 \
            --user "$(id -u)":"$(id -g)" \
            --entrypoint "/app/${BIN_DIR}/$(basename "${0}")" \
            --workdir /app \
            --env HOST_ROOT_DIR="${ROOT_DIR}" \
            "${DOCKER_IMAGE_NAME}" \
            "${@}"

    exit 0
fi
