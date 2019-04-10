#!/bin/sh
rm logs/*
rm tmp/cache/models/*
rm tmp/cache/persistent/*
rm tmp/cache/views/*
rm tmp/cache/twigView/*
rm tmp/sessions/*
rm tmp/tests/*
cat /dev/null > tmp/cache/debug_kit.sqlite
