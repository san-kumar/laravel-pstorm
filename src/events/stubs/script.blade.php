<script>
let fn = () => {
    let tabs = document.querySelector('.phpdebugbar-tab');
    if(!tabs) return setTimeout(() => fn(), 100);

    let newTab = `
        <div class="dropdown m-0 p-0">
            <a class="phpdebugbar-tab text-dark fw-bold" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" href="#"> PHPSTORM </a>
            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                <li class="fw-bold p-2 bg-light">Views</li>
                @foreach($views as $view)
                    <li><a class="dropdown-item px-3" href="{{ $view['url'] }}">{{ $view['name'] }}</a></li>
                @endforeach

                <li class="fw-bold p-2 bg-light">Controller</li>
                <li><a class="dropdown-item px-3" href="{{ $controller['url'] }}">{{ $controller['name'] }}</a></li>
            </ul>
        </div>
    `;
    //add newTab after last tab
    tabs.insertAdjacentHTML('afterend', newTab);
};

fn();
</script>
