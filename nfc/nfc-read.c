/*-
 * Free/Libre Near Field Communication (NFC) library
 *
 * Libnfc historical contributors:
 * Copyright (C) 2009      Roel Verdult
 * Copyright (C) 2009-2013 Romuald Conty
 * Copyright (C) 2010-2012 Romain Tartière
 * Copyright (C) 2010-2013 Philippe Teuwen
 * Copyright (C) 2012-2013 Ludovic Rousseau
 * See AUTHORS file for a more comprehensive list of contributors.
 * Additional contributors of this file:
 * Copyright (C) 2020      Adam Laurie
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *  1) Redistributions of source code must retain the above copyright notice,
 *  this list of conditions and the following disclaimer.
 *  2 )Redistributions in binary form must reproduce the above copyright
 *  notice, this list of conditions and the following disclaimer in the
 *  documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * Note that this license only applies on the examples, NFC library itself is under LGPL
 *
 */

/**
 * @file nfc-read.c
 * @continuous running card reader
 * @with LED indicator and beep
 */

#ifdef HAVE_CONFIG_H
#  include "config.h"
#endif // HAVE_CONFIG_H

#include <err.h>
#include <inttypes.h>
#include <signal.h>
#include <stdio.h>
#include <stddef.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <nfc/nfc.h>
#include <nfc/nfc-types.h>

#include "utils/nfc-utils.h"
#include <wiringPi.h>
#include <curl/curl.h>

#define MAX_DEVICE_COUNT 16

static nfc_device *pnd = NULL;
static nfc_context *context;


void gpio_setup (void)
{
    wiringPiSetupGpio();
    pinMode(23,OUTPUT);
    pinMode(24,OUTPUT);
}

void beep (void)
{
   digitalWrite(24,1);     // beep
   usleep(250000);         // 250ms
   digitalWrite(24,0);
}

// send card ID number to server
// returns 1 if delivered ok
// else returns 0

int get_request (unsigned long card_id)
{
  CURL *curl;
  CURLcode res;
  char url[100];
  sprintf(url, "http://admin.rml/nfc-card.php?a=%lu", card_id); 
  long r_code;
  int result = 0;
  curl = curl_easy_init();
  if(curl) {
    curl_easy_setopt(curl, CURLOPT_URL, &url);
    curl_easy_setopt(curl, CURLOPT_FOLLOWLOCATION, 1L);

    // curl_easy_setopt(myHandle, CURLOPT_WRITEFUNCTION, WriteMemoryCallback);
    // curl_easy_setopt(myHandle, CURLOPT_WRITEDATA, (void*)&output);

    res = curl_easy_perform(curl);
    if(res == CURLE_OK) {
        res = curl_easy_getinfo(curl, CURLINFO_CONTENT_LENGTH_DOWNLOAD_T, &r_code);
    //        fprintf(stderr, "Length of   : %d\n", r_code);
    // r_code is 4 if server sends "Ok\r\n"
    // r_code is 9 if server sends "Unknown\r\n"
        if (r_code == 4) beep();
    } else {
    //        fprintf(stderr, "curl_easy_perform() failed: %s\n",
    //               curl_easy_strerror(res));
    }
    curl_easy_cleanup(curl);
  }
  return (result);
}

// get the UID (NFCID1) from the struct
// and return it as a long int
// if UID > 4 bytes, use just the last 4 bytes
unsigned long read_nfc_id(const nfc_target *nt)
{
  int i;
  unsigned long j = 0;
  size_t Usz;
  uint8_t Uid[10];
    Usz = nt->nti.nai.szUidLen;
    for (i=0; i<Usz; i++)
        Uid[i] = nt->nti.nai.abtUid[i];
    for (i=Usz-4; i<Usz; i++) {
        j *= 256;
        j += Uid[i]; 
    }
    return (j);
}

static void stop_polling(int sig)
{
  (void) sig;
  if (pnd != NULL)
    nfc_abort_command(pnd);
  else {
    nfc_exit(context);
    exit(EXIT_FAILURE);
  }
}

void
print_nfc_target(const nfc_target *pnt, bool verbose)
{
  char *s;
  str_nfc_target(&s, pnt, verbose);
  printf("%s", s);
  nfc_free(s);
}

static void
print_usage(const char *progname)
{
  printf("usage: %s [-v]\n", progname);
  printf("  -v\t verbose display\n");
}

int
main(int argc, const char *argv[])
{
  bool verbose = false;

  signal(SIGINT, stop_polling);
  gpio_setup();

  // Display libnfc version
  const char *acLibnfcVersion = nfc_version();

//   printf("%s uses libnfc %s\n", argv[0], acLibnfcVersion);
  if (argc != 1) {
    if ((argc == 2) && (0 == strcmp("-v", argv[1]))) {
      verbose = true;
    } else {
      print_usage(argv[0]);
      exit(EXIT_FAILURE);
    }
  }

  const uint8_t uiPollNr = 20;
  const uint8_t uiPeriod = 2;
  const nfc_modulation nmModulations[2] = {
    { .nmt = NMT_ISO14443A, .nbr = NBR_106 },
    { .nmt = NMT_ISO14443B, .nbr = NBR_106 },
//    { .nmt = NMT_FELICA, .nbr = NBR_212 },
//    { .nmt = NMT_FELICA, .nbr = NBR_424 },
//    { .nmt = NMT_JEWEL, .nbr = NBR_106 },
//    { .nmt = NMT_ISO14443BICLASS, .nbr = NBR_106 },
  };
  const size_t szModulations = 2;

  nfc_target nt;
  unsigned long nfc_id;
  int res = 0;

  nfc_init(&context);
  if (context == NULL) {
    ERR("Unable to init libnfc (malloc)");
    exit(EXIT_FAILURE);
  }

  while (1)     {
    
    signal(SIGINT, stop_polling);
    pnd = nfc_open(context, NULL);

    if (pnd == NULL) {
        ERR("%s", "Unable to open NFC device.");
        nfc_exit(context);
        exit(EXIT_FAILURE);
    }

    if (nfc_initiator_init(pnd) < 0) {
        nfc_perror(pnd, "nfc_initiator_init");
        nfc_close(pnd);
        nfc_exit(context);
        exit(EXIT_FAILURE);
    }

//    printf("NFC reader: %s opened\n", nfc_device_get_name(pnd));
//    printf("NFC device will poll during %ld ms (%u pollings of %lu ms for %" PRIdPTR " modulations)\n", (unsigned long) uiPollNr * szModulations * uiPeriod * 150, uiPollNr, (unsigned long) uiPeriod * 150, szModulations);
    if ((res = nfc_initiator_poll_target(pnd, nmModulations, szModulations, uiPollNr, uiPeriod, &nt))  < 0) {
        nfc_perror(pnd, "nfc_initiator_poll_target");
        nfc_close(pnd);
        nfc_exit(context);
        exit(EXIT_FAILURE);
    }

    if (res) {
        digitalWrite(23,1);     // LED on
    }

    if (res > 0) {
//        print_nfc_target(&nt, verbose);
        nfc_id = read_nfc_id(&nt);
//        printf("Id is %d\n",nfc_id);
//        printf("Waiting for card removing...");
        fflush(stdout);
        get_request(nfc_id);
        while (0 == nfc_initiator_target_is_present(pnd, NULL)) {}
//        nfc_perror(pnd, "nfc_initiator_target_is_present");
//        printf("done.\n");
    } else {
//        printf("No target found.\n");
    }

    digitalWrite(23,0);         // LED off

    nfc_close(pnd);
  }     // end while 

  nfc_exit(context);
  exit(EXIT_SUCCESS);
}
