export type WpMediaPickerLabels = {
  title: string;
  button: string;
};

type WpMediaAttachment = {
  url?: string;
};

type WpMediaSelection = {
  first: () => { toJSON: () => WpMediaAttachment };
};

type WpMediaFrame = {
  on: (event: string, callback: () => void) => WpMediaFrame;
  open: () => void;
  state: () => { get: (key: string) => WpMediaSelection };
};

type WpMediaApi = {
  media: (args: {
    title: string;
    button: { text: string };
    multiple: boolean;
    library: { type: string };
  }) => WpMediaFrame;
};

function wpMediaApi(): WpMediaApi | null {
  if (typeof window === 'undefined') {
    return null;
  }
  const wp = (window as Window & { wp?: { media?: WpMediaApi['media'] } }).wp;
  return wp?.media ? { media: wp.media } : null;
}

/** Open the WordPress media library and return the selected image URL. */
export function pickWpMediaImage(labels: WpMediaPickerLabels): Promise<string | null> {
  const api = wpMediaApi();
  if (!api) {
    return Promise.resolve(null);
  }

  return new Promise((resolve) => {
    const frame = api.media({
      title: labels.title,
      button: { text: labels.button },
      multiple: false,
      library: { type: 'image' },
    });

    frame.on('select', () => {
      const attachment = frame.state().get('selection').first().toJSON();
      const url = typeof attachment.url === 'string' ? attachment.url.trim() : '';
      resolve(url !== '' ? url : null);
    });

    frame.open();
  });
}
