# Install script for first time setup
# Or to reset the project to a clean state

# Check to see if docker is installed and running
if ! [ -x "$(command -v docker)" ]; then
    echo "Docker is not installed. Please install docker and try again."
    exit 1
fi
if ! docker info >/dev/null 2>&1; then
    echo "Docker is not running. Please start docker and try again."
    exit 1
fi
if ! [ -x "$(command -v npm)" ]; then
    echo "npm is not installed. Please install npm and try again."
    exit 1
fi
if ! npm -v | grep -qE '^[0-9]{2,}'; then
    echo "npm version 10 or greater is not installed. Please install npm version 10 or greater and try again."
    exit 1
fi

# Remove vendor and node_modules directories if they exist
if [ -d "vendor" ]; then
    echo "Removing vendor directory..."
    vendor/bin/sail down -v
    rm -rf vendor
    rm composer.lock
fi
if [ -d "node_modules" ]; then
    echo "Removing node_modules directory..."
    rm -rf node_modules
fi

# Copy .env.example to .env if it doesn't exist
if [ -f ".env" ]; then
    echo ".env already exists."
else
    echo "Copying .env.example to .env..."
    cp .env.example .env
    # Ask for desired database name
    echo "What would you like to name your database?"
    read -p "Database name: " database_name

    # Detect operating system
    if [[ "$OSTYPE" == "darwin"* ]]; then
        # macOS uses BSD sed
        sed -i "" "s/DB_DATABASE=livv_stack/DB_DATABASE=$database_name/g" .env
    elif [[ "$OSTYPE" == "linux-gnu"* ]]; then
        # Linux uses GNU sed
        sed -i "s/DB_DATABASE=livv_stack/DB_DATABASE=$database_name/g" .env
    else
        # Unknown OS, use Perl as a fallback
        perl -i -pe "s/DB_DATABASE=livv_stack/DB_DATABASE=$database_name/g" .env
    fi
fi


# Install composer dependencies
echo "Installing composer dependencies..."
docker run --rm --interactive --tty --name tmp-composer-install --volume $PWD:/app composer install --ignore-platform-reqs --no-scripts

# Run containers
echo "Building and running containers..."
docker compose down -v
vendor/bin/sail build --no-cache
vendor/bin/sail up -d

# Generate application key, if it doesn't exist
if grep -q "^APP_KEY=$" .env; then
    echo "APP_KEY is not set. Generating a new APP_KEY..."
    vendor/bin/sail artisan key:generate
else
    echo "APP_KEY is already set."
fi

# Run migrations
echo "Running migrations..."
sleep 10
vendor/bin/sail artisan migrate:fresh

# Re-run composer install do to post-install scripts
echo "Re-running composer install..."
vendor/bin/sail composer install

# Generate ide helper files
echo "Generating ide helper files..."

vendor/bin/sail composer ide-helpers

# If .git is still pointing to livv-stack, remove it and reinitialize git
if [ -d ".git" ]; then
    git_url=$(git config --get remote.origin.url)
    repo_name=$(basename -s .git "$git_url")

    # Check if the repository name matches
    if [ "$repo_name" = "livv-stack" ]; then
        echo "Deleting livv-stack .git directory..."
        rm -rf .git

        echo "Reinitializing git repository..."
        git init

        git add .
        git commit -m "Initial commit"

        # If this occurs then likely its the first time the script is being run
        # Move the github workflow yaml file to a disabled location
        if [ -f ".github/workflows/laravel_ci.yml" ]; then
            echo "Disabling github workflow..."
            mv .github/workflows/laravel_ci.yml .github/workflows/laravel_ci.yml.disabled
        fi
    else
        echo "Repository name does not match livv-stack. Skipping git reinitialization..."
    fi
else
    echo "No .git directory found. Initializing git repository..."
    git init

    git add .
    git commit -m "Initial commit"
fi

# Install npm dependencies
echo "Installing npm dependencies..."
npm install

# Run asset watcher
printf "\n\n###################################################\n"
echo "##            You're all set!                    ##"
echo "##    Go visit 'localhost' in your browser       ##"
printf "###################################################\n\n"
npm run dev


